<?php

namespace App\Http\Controllers;

use App\Support\ChunkedUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Menerima satu potongan file backup untuk restore (lihat ChunkedUpload).
 * Potongan wajib dikirim berurutan; potongan yang dikirim ulang (retry
 * setelah koneksi putus) diterima tanpa ditulis dua kali.
 */
class RestoreChunkUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $data = $request->validate([
            'token' => ['nullable', 'string'],
            'index' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1', 'max:1000000'],
            'size'  => ['required', 'integer', 'min:1'],
            'name'  => ['required', 'string', 'max:255'],
            'chunk' => ['required', 'file'],
        ]);

        $chunk = $request->file('chunk');

        if ($chunk->getSize() > ChunkedUpload::CHUNK_BYTES) {
            return $this->fail('Potongan file terlalu besar.');
        }

        $index = (int) $data['index'];

        if ($index === 0) {
            if (! str_ends_with(strtolower($data['name']), '.zip')) {
                return $this->fail('File harus berformat .zip.');
            }

            // File upload + backup pengaman + hasil ekstrak ≈ 3× ukuran backup.
            $free = @disk_free_space(ChunkedUpload::directory());
            if ($free !== false && $free < (int) $data['size'] * 3) {
                return $this->fail(sprintf(
                    'Ruang disk server tidak cukup: butuh sekitar %s, tersedia %s.',
                    $this->formatBytes((int) $data['size'] * 3),
                    $this->formatBytes((int) $free),
                ));
            }

            ChunkedUpload::discard($data['token'] ?? null);
            $token = ChunkedUpload::newToken();
            $meta = [
                'name' => $data['name'],
                'size' => (int) $data['size'],
                'total' => (int) $data['total'],
                'next' => 0,
                'user_id' => $request->user()->getAuthIdentifier(),
            ];
            @unlink(ChunkedUpload::partPath($token));
        } else {
            $token = $data['token'] ?? null;
            $meta = ChunkedUpload::isValidToken($token) ? ChunkedUpload::readMeta($token) : null;

            if (! $meta || $meta['user_id'] !== $request->user()->getAuthIdentifier()) {
                return $this->fail('Sesi upload tidak ditemukan atau kedaluwarsa. Pilih file lagi.');
            }
        }

        if ($index < $meta['next']) {
            // Potongan ini sudah diterima (browser mengirim ulang setelah gangguan).
            return response()->json(['token' => $token, 'done' => false]);
        }

        if ($index > $meta['next'] || $index >= $meta['total']) {
            return $this->fail('Urutan potongan file tidak sesuai. Pilih file lagi.');
        }

        $in = fopen($chunk->getRealPath(), 'rb');
        $out = fopen(ChunkedUpload::partPath($token), 'ab');
        $written = ($in && $out) ? stream_copy_to_stream($in, $out) : false;
        $in && fclose($in);
        $out && fclose($out);

        if ($written === false || $written !== $chunk->getSize()) {
            return $this->fail('Gagal menulis file di server. Periksa ruang disk server.');
        }

        $meta['next']++;
        ChunkedUpload::writeMeta($token, $meta);

        if ($meta['next'] < $meta['total']) {
            return response()->json(['token' => $token, 'done' => false]);
        }

        // Potongan terakhir: pastikan ukuran utuh dan memang file ZIP.
        $part = ChunkedUpload::partPath($token);
        clearstatcache(true, $part);
        $handle = fopen($part, 'rb');
        $magic = $handle ? fread($handle, 4) : '';
        $handle && fclose($handle);

        if (filesize($part) !== $meta['size'] || $magic !== "PK\x03\x04") {
            ChunkedUpload::discard($token);

            return $this->fail('File yang diterima tidak utuh atau bukan ZIP. Silakan unggah ulang.');
        }

        rename($part, ChunkedUpload::directory() . '/' . $token . '.zip');

        return response()->json(['token' => $token, 'done' => true]);
    }

    private function fail(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1073741824
            ? number_format($bytes / 1073741824, 1) . ' GB'
            : number_format($bytes / 1048576, 0) . ' MB';
    }
}
