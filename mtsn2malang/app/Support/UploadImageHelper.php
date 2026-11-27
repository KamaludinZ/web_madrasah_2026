<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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

                    // Sisi terpanjang dibatasi sesuai folder (logo 512px, foto
                    // berita 1600px, dst.) lalu disimpan sebagai WEBP — foto
                    // kamera 4000+ px tidak lagi tersimpan utuh (1–1,5 MB).
                    $maxDimension = ImageOptimizer::maxDimensionFor($targetDirectory . '/x');
                    $newFileName = $safeName . '-' . now()->timestamp . '.webp';
                    $relativePath = $targetDirectory . '/' . $newFileName;
                    $fullPath = storage_path('app/public/' . $relativePath);

                    if (! is_dir(dirname($fullPath))) {
                        mkdir(dirname($fullPath), 0755, true);
                    }

                    // WEBP yang sudah kecil disimpan apa adanya (tanpa turun kualitas).
                    $size = @getimagesize($file->getRealPath());
                    if (($mimeType === 'image/webp' || $ext === 'webp') && $size
                        && max($size[0], $size[1]) <= $maxDimension
                        && $file->getSize() <= ImageOptimizer::SMALL_FILE_BYTES) {
                        copy($file->getRealPath(), $fullPath);

                        return $relativePath;
                    }

                    if (ImageOptimizer::toWebp($file->getRealPath(), $fullPath, $maxDimension)) {
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
