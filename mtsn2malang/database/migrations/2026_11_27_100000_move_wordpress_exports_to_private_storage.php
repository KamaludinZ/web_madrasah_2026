<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

/**
 * File ekspor WordPress (WXR) dulu diunggah ke disk "public" sehingga bisa
 * diunduh lewat /storage/wordpress-imports/… — isinya seluruh konten lama
 * termasuk email penulis. Pindahkan ke disk privat "local".
 */
return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $local = Storage::disk('local');

        foreach ($public->allFiles('wordpress-imports') as $path) {
            $local->writeStream($path, $public->readStream($path));
            $public->delete($path);
        }

        $public->deleteDirectory('wordpress-imports');
    }

    public function down(): void
    {
        // Tidak dikembalikan ke folder publik.
    }
};
