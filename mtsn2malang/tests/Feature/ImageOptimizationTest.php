<?php

namespace Tests\Feature;

use App\Support\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Gambar upload diperkecil (audit Nov 2026: foto berita 4569×3427 / 1,5 MB,
 * logo 2866×2832 tampil di setiap halaman; Beranda 4,7 MB).
 */
class ImageOptimizationTest extends TestCase
{
    /** Foto "kamera" berisi noise agar ukurannya realistis. */
    private function photo(string $path, int $width, int $height, string $format = 'jpeg'): string
    {
        $image = imagecreatetruecolor($width, $height);
        for ($i = 0; $i < 4000; $i++) {
            $color = imagecolorallocate($image, random_int(0, 255), random_int(0, 255), random_int(0, 255));
            imagefilledellipse($image, random_int(0, $width), random_int(0, $height), random_int(20, 200), random_int(20, 200), $color);
        }
        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0755, true);
        $format === 'webp' ? imagewebp($image, $full, 100) : imagejpeg($image, $full, 100);

        return $full;
    }

    public function test_max_dimension_depends_on_folder(): void
    {
        $this->assertSame(512, ImageOptimizer::maxDimensionFor('settings/branding/logo.webp'));
        $this->assertSame(1600, ImageOptimizer::maxDimensionFor('news/thumbnails/a.webp'));
        $this->assertSame(800, ImageOptimizer::maxDimensionFor('gtk/guru.webp'));
        $this->assertSame(ImageOptimizer::DEFAULT_MAX, ImageOptimizer::maxDimensionFor('banners/x.webp'));
    }

    public function test_upload_is_scaled_down_and_converted_to_webp(): void
    {
        Storage::fake('public');
        $source = $this->photo('src/kamera.jpg', 4000, 3000);
        $target = Storage::disk('public')->path('news/thumbnails/hasil.webp');

        $this->assertTrue(ImageOptimizer::toWebp($source, $target, ImageOptimizer::maxDimensionFor('news/thumbnails/hasil.webp')));

        [$width, $height, $type] = getimagesize($target);
        $this->assertSame([1600, 1200, IMAGETYPE_WEBP], [$width, $height, $type]);
        $this->assertLessThan(filesize($source), filesize($target));
    }

    public function test_command_shrinks_old_images_in_place_keeping_name_and_format(): void
    {
        Storage::fake('public');
        $logo = $this->photo('settings/branding/logo-besar.webp', 2866, 2832, 'webp');
        $thumb = $this->photo('news/thumbnails/foto.jpg', 4569, 3427);
        $sizeBefore = filesize($thumb);

        $this->artisan('images:optimize', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame($sizeBefore, filesize($thumb), 'Dry-run tidak mengubah file.');

        $this->artisan('images:optimize')->assertSuccessful();
        clearstatcache();

        $this->assertSame([512, 506, IMAGETYPE_WEBP], array_slice(getimagesize($logo), 0, 3));
        $this->assertSame([1600, 1200, IMAGETYPE_JPEG], array_slice(getimagesize($thumb), 0, 3));
        $this->assertLessThan($sizeBefore, filesize($thumb));

        // Dijalankan ulang: tidak ada lagi yang perlu diubah.
        $this->artisan('images:optimize')->expectsOutputToContain('Diperkecil: 0 gambar')->assertSuccessful();
    }
}
