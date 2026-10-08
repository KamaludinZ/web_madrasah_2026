<?php

namespace App\Http\Controllers;

use App\Models\AppBackup;
use App\Services\BackupService;

/**
 * Unduh file backup lewat request HTTP biasa (bukan aksi Livewire).
 *
 * Aksi Livewire yang mengembalikan download memuat SELURUH file ke memori lalu
 * meng-encode-nya base64 ke respons JSON — backup berukuran ratusan MB langsung
 * melewati memory_limit PHP. Di sini file dialirkan per potongan dari disk.
 */
class BackupDownloadController extends Controller
{
    public function __invoke(AppBackup $backup, BackupService $service)
    {
        abort_unless(auth()->user()?->hasRole('super_admin'), 403);
        abort_if($backup->status !== 'completed' || blank($backup->filename), 404);

        $service->assertSafeFilename($backup->filename);
        $path = $service->absolutePathFor($backup->filename);
        abort_unless(is_file($path), 404);

        // Output buffering (bawaan sebagian hosting) akan menampung seluruh
        // isi file di memori sebelum dikirim — matikan dulu.
        while (! app()->runningInConsole() && ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download($path, $backup->filename);
    }
}
