<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Models\GalleryPhoto;
use App\Models\GalleryPost;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Isi field galeri dari relasi yang sudah ada
        $galleryPost = GalleryPost::where('news_id', $this->record->id)->first();
        if ($galleryPost) {
            $data['gallery_title']       = $galleryPost->title;
            $data['gallery_description'] = $galleryPost->description;
        }

        // Prefill tags dari Spatie HasTags (nama tag sebagai array string)
        try {
            $data['tags'] = $this->record->tags->pluck('name')->toArray();
        } catch (\Throwable) {
            $data['tags'] = [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hapus semua field galeri & tags dari data news (diproses manual di afterSave)
        unset(
            $data['gallery_title'],
            $data['gallery_description'],
            $data['gallery_photos_upload'],
            $data['gallery_photos_to_delete'],
            $data['tags']
        );
        return $data;
    }

    protected function afterSave(): void
    {
        $this->deleteMarkedPhotos();
        $this->saveOrUpdateGalleryPost($this->record->id);
        $this->syncTags();
    }

    protected function deleteMarkedPhotos(): void
    {
        $state    = $this->form->getRawState();
        $toDelete = $state['gallery_photos_to_delete'] ?? [];

        // Bisa berupa JSON string (dari Alpine $wire.set) atau array
        if (is_string($toDelete)) {
            $toDelete = json_decode($toDelete, true) ?? [];
        }

        if (empty($toDelete) || ! is_array($toDelete)) {
            return;
        }

        foreach ($toDelete as $photoId) {
            $photoId = (int) $photoId;
            if ($photoId <= 0) continue;

            $photo = GalleryPhoto::find($photoId);
            if ($photo) {
                Storage::disk('public')->delete($photo->file_path);
                $photo->delete();
            }
        }
    }

    protected function syncTags(): void
    {
        $tags = $this->form->getRawState()['tags'] ?? [];
        $this->record->syncTags(is_array($tags) ? $tags : []);
    }

    protected function saveOrUpdateGalleryPost(int $newsId): void
    {
        $data = $this->form->getRawState();

        $galleryTitle  = trim((string) ($data['gallery_title'] ?? ''));
        $galleryDesc   = trim((string) ($data['gallery_description'] ?? ''));
        $uploadedFiles = $data['gallery_photos_upload'] ?? [];

        // Cari gallery post yang sudah ada
        $galleryPost = GalleryPost::where('news_id', $newsId)->first();

        if (empty($galleryTitle)) {
            // Jika judul dikosongkan, hapus gallery post beserta fotonya
            if ($galleryPost) {
                foreach ($galleryPost->photos as $photo) {
                    Storage::disk('public')->delete($photo->file_path);
                }
                $galleryPost->delete();
            }
            return;
        }

        // Update atau buat gallery post
        if ($galleryPost) {
            $galleryPost->update([
                'title'       => $galleryTitle,
                'description' => $galleryDesc ?: null,
            ]);
        } else {
            $galleryPost = GalleryPost::create([
                'news_id'     => $newsId,
                'title'       => $galleryTitle,
                'description' => $galleryDesc ?: null,
            ]);
        }

        // Proses foto baru yang diupload (jika ada)
        if (! empty($uploadedFiles)) {
            $currentMaxOrder = $galleryPost->photos()->max('order') ?? -1;
            $order = $currentMaxOrder + 1;

            foreach ($uploadedFiles as $tempPath) {
                if (empty($tempPath)) {
                    continue;
                }

                // Skip jika path sudah ada di gallery (bukan temp)
                if (str_starts_with($tempPath, 'gallery/') && ! str_contains($tempPath, 'temp')) {
                    continue;
                }

                try {
                    $finalPath = $this->convertToWebpAndMove($tempPath, $order);
                    if ($finalPath) {
                        GalleryPhoto::create([
                            'gallery_post_id' => $galleryPost->id,
                            'file_path'       => $finalPath,
                            'order'           => $order,
                        ]);
                        $order++;
                    }
                } catch (Throwable $e) {
                    Log::error('Gallery photo update failed', [
                        'temp_path' => $tempPath,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    protected function convertToWebpAndMove(string $tempPath, int $order): ?string
    {
        $fullTemp = storage_path('app/public/' . $tempPath);

        if (! file_exists($fullTemp)) {
            return null;
        }

        $targetDir = 'gallery';
        $safeName  = 'gallery-' . now()->timestamp . '-' . $order;
        $finalName = $safeName . '.webp';
        $finalPath = $targetDir . '/' . $finalName;
        $fullFinal = storage_path('app/public/' . $finalPath);

        if (! is_dir(dirname($fullFinal))) {
            mkdir(dirname($fullFinal), 0755, true);
        }

        $ext = strtolower(pathinfo($fullTemp, PATHINFO_EXTENSION));

        if ($ext === 'webp') {
            rename($fullTemp, $fullFinal);
            return $finalPath;
        }

        $driver = null;
        if (extension_loaded('gd')) {
            try { $driver = new GdDriver(); } catch (Throwable) { $driver = null; }
        }
        if (! $driver && extension_loaded('imagick')) {
            try { $driver = new ImagickDriver(); } catch (Throwable) { $driver = null; }
        }

        if ($driver) {
            $manager = new ImageManager($driver);
            $image   = $manager->read($fullTemp);
            $encoded = $image->encode(new WebpEncoder(82));
            file_put_contents($fullFinal, (string) $encoded);
            @unlink($fullTemp);
            return $finalPath;
        }

        // Fallback
        $fallbackPath = $targetDir . '/' . $safeName . '.' . $ext;
        rename($fullTemp, storage_path('app/public/' . $fallbackPath));
        return $fallbackPath;
    }
}
