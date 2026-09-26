<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\NewsResource;
use App\Models\GalleryPhoto;
use App\Models\GalleryPost;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

class CreateNews extends CreateRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = NewsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hapus field galeri & tags dari data news (tidak ada kolomnya di tabel news)
        unset($data['gallery_title'], $data['gallery_description'], $data['gallery_photos_upload'], $data['tags']);
        return NewsResource::enforcePermissions($data);
    }

    protected function afterCreate(): void
    {
        $this->saveGalleryPost($this->record->id);
        $this->syncTags();
        app(\App\Services\SubscriptionMailerService::class)->queueIfPublished($this->record);
    }

    protected function syncTags(): void
    {
        $tags = $this->form->getRawState()['tags'] ?? [];
        // syncTags([]) akan menghapus semua tag jika kosong
        $this->record->syncTags(is_array($tags) ? $tags : []);
    }

    protected function saveGalleryPost(int $newsId): void
    {
        $data = $this->form->getRawState();

        $galleryTitle  = trim((string) ($data['gallery_title'] ?? ''));
        $galleryDesc   = trim((string) ($data['gallery_description'] ?? ''));
        $uploadedFiles = $data['gallery_photos_upload'] ?? [];

        if (empty($galleryTitle) || empty($uploadedFiles)) {
            return;
        }

        // Buat GalleryPost
        $galleryPost = GalleryPost::create([
            'news_id'     => $newsId,
            'title'       => $galleryTitle,
            'description' => $galleryDesc ?: null,
        ]);

        $this->processAndSavePhotos($galleryPost, $uploadedFiles);
    }

    protected function processAndSavePhotos(GalleryPost $galleryPost, array $uploadedFiles): void
    {
        $order = 0;
        foreach ($uploadedFiles as $tempPath) {
            if (empty($tempPath)) {
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
                Log::error('Gallery photo save failed', [
                    'temp_path' => $tempPath,
                    'error'     => $e->getMessage(),
                ]);
            }
        }
    }

    protected function convertToWebpAndMove(string $tempPath, int $order): ?string
    {
        $disk     = Storage::disk('public');
        $fullTemp = storage_path('app/public/' . $tempPath);

        if (! file_exists($fullTemp)) {
            return null;
        }

        $targetDir  = 'gallery';
        $safeName   = 'gallery-' . now()->timestamp . '-' . $order;
        $finalName  = $safeName . '.webp';
        $finalPath  = $targetDir . '/' . $finalName;
        $fullFinal  = storage_path('app/public/' . $finalPath);

        if (! is_dir(dirname($fullFinal))) {
            mkdir(dirname($fullFinal), 0755, true);
        }

        $ext = strtolower(pathinfo($fullTemp, PATHINFO_EXTENSION));

        // Jika sudah webp, pindahkan saja
        if ($ext === 'webp') {
            rename($fullTemp, $fullFinal);
            return $finalPath;
        }

        // Konversi ke WebP
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
            // Hapus file temp
            @unlink($fullTemp);
            return $finalPath;
        }

        // Fallback: pindahkan tanpa konversi
        rename($fullTemp, storage_path('app/public/' . $targetDir . '/' . $safeName . '.' . $ext));
        return $targetDir . '/' . $safeName . '.' . $ext;
    }
}
