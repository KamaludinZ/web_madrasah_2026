<?php

namespace App\Jobs;

use App\Models\Banner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

class ConvertImageToWebpJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public array $backoff = [60, 180, 300, 600, 1200];

    public function __construct(public int $bannerId)
    {
    }

    public function handle(): void
    {
        $banner = Banner::find($this->bannerId);

        if (! $banner || ! $banner->image) {
            return;
        }

        $path = ltrim($banner->image, '/');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return;
        }

        if (! Storage::disk('public')->exists($path)) {
            return;
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

        if (! $driver) {
            throw new \RuntimeException('No usable GD/Imagick driver for WebP conversion.');
        }

        $manager = new ImageManager($driver);
        $sourceAbsolutePath = storage_path('app/public/' . $path);
        $image = $manager->read($sourceAbsolutePath);
        $encoded = $image->encode(new WebpEncoder(82));

        $baseWithoutExt = preg_replace('/\.(jpg|jpeg|png)$/i', '', $path) ?? $path;
        $webpPath = $baseWithoutExt . '.webp';
        $webpAbsolutePath = storage_path('app/public/' . $webpPath);

        if (! is_dir(dirname($webpAbsolutePath))) {
            mkdir(dirname($webpAbsolutePath), 0755, true);
        }

        file_put_contents($webpAbsolutePath, (string) $encoded);

        // Update banner ke path webp
        $banner->update(['image' => $webpPath]);

        // Hapus file lama jika berbeda
        if ($webpPath !== $path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::warning('ConvertImageToWebpJob failed', [
            'banner_id' => $this->bannerId,
            'error' => $exception->getMessage(),
        ]);
    }
}
