<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class UploadImageHelper
{
    public static function webpFileUpload(
        string $name,
        string $label,
        string $directory,
        bool $required = false
    ): FileUpload {
        $component = FileUpload::make($name)
            ->label($label)
            ->image()
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->maxSize(5120)
            ->acceptedFileTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/jpg',
            ])
            ->validationMessages([
                'max' => 'Upload gagal: ukuran file terlalu besar. Maksimal 5MB.',
                'mimes' => 'Upload gagal: format file tidak didukung. Gunakan JPG, PNG, atau WEBP.',
                'image' => 'Upload gagal: file yang dipilih bukan gambar valid.',
            ])
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, FileUpload $component) use ($directory) {
                try {
                    $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeName = str($baseName)->slug()->value() ?: 'image';
                    $targetDirectory = trim($directory, '/');

                    // SVG ditolak: file SVG bisa berisi skrip yang berjalan di domain
                    // website saat dibuka langsung (stored XSS). Filter berbasis regex
                    // mudah diakali, jadi format ini tidak diterima sama sekali.
                    $mimeType = strtolower((string) $file->getMimeType());
                    $ext = strtolower((string) $file->getClientOriginalExtension());

                    if ($mimeType === 'image/svg+xml' || $ext === 'svg') {
                        throw ValidationException::withMessages([
                            $component->getStatePath() => 'Upload gagal: format SVG tidak diizinkan. Gunakan JPG, PNG, atau WEBP.',
                        ]);
                    }

                    // Jika file sudah WEBP, simpan apa adanya tanpa konversi lagi
                    if ($mimeType === 'image/webp' || $ext === 'webp') {
                        $webpFileName = $safeName . '-' . now()->timestamp . '.webp';
                        $webpRelativePath = $targetDirectory . '/' . $webpFileName;
                        $webpFullPath = storage_path('app/public/' . $webpRelativePath);

                        if (! is_dir(dirname($webpFullPath))) {
                            mkdir(dirname($webpFullPath), 0755, true);
                        }

                        copy($file->getRealPath(), $webpFullPath);

                        return $webpRelativePath;
                    }

                    // Raster (jpg/jpeg/png): coba konversi ke webp
                    $newFileName = $safeName . '-' . now()->timestamp . '.webp';
                    $relativePath = $targetDirectory . '/' . $newFileName;
                    $fullPath = storage_path('app/public/' . $relativePath);

                    if (! is_dir(dirname($fullPath))) {
                        mkdir(dirname($fullPath), 0755, true);
                    }

                    $driver = null;

                    if (extension_loaded('gd')) {
                        try {
                            $driver = new GdDriver();
                        } catch (Throwable) {
                            $driver = null;
                        }
                    }

                    if (! $driver && extension_loaded('imagick')) {
                        try {
                            $driver = new ImagickDriver();
                        } catch (Throwable) {
                            $driver = null;
                        }
                    }

                    if ($driver) {
                        $manager = new ImageManager($driver);
                        $image = $manager->read($file->getRealPath());
                        $encoded = $image->encode(new WebpEncoder(82));
                        file_put_contents($fullPath, (string) $encoded);

                        return $relativePath;
                    }

                    // Fallback tanpa driver image yang usable:
                    // simpan file asli (ekstensi dipertahankan) agar upload tidak gagal.
                    $originalExt = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                    $safeExt = in_array($originalExt, ['jpg', 'jpeg', 'png', 'webp'], true) ? $originalExt : 'jpg';
                    $fallbackFileName = $safeName . '-' . now()->timestamp . '.' . $safeExt;
                    $fallbackRelativePath = $targetDirectory . '/' . $fallbackFileName;
                    $fallbackFullPath = storage_path('app/public/' . $fallbackRelativePath);

                    if (! is_dir(dirname($fallbackFullPath))) {
                        mkdir(dirname($fallbackFullPath), 0755, true);
                    }

                    copy($file->getRealPath(), $fallbackFullPath);

                    return $fallbackRelativePath;
                } catch (ValidationException $e) {
                    throw $e;
                } catch (Throwable $e) {
                    Log::error('Image upload conversion failed', [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ]);

                    throw ValidationException::withMessages([
                        $component->getStatePath() => 'Upload gagal: sistem tidak bisa memproses gambar saat ini. Coba ulangi atau hubungi admin server.',
                    ]);
                }
            });

        if ($required) {
            $component = $component->required();
        }

        return $component;
    }
}
