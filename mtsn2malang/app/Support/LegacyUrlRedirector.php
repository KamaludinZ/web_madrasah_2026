<?php

namespace App\Support;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Mengalihkan (301) alamat lama website WordPress ke halaman baru, mis.
 * /category/berita/ → /berita?kategori=berita. Link lama masih tersebar di
 * Google, WhatsApp, dan media sosial; tanpa ini pengunjung mendapat 404.
 *
 * Dipanggil HANYA saat sebuah alamat tidak ditemukan (404), sehingga tidak
 * menambah beban pada halaman lain. Mengembalikan null bila tidak dikenali.
 */
final class LegacyUrlRedirector
{
    /** Prefix halaman statis baru yang mungkin dulunya halaman WordPress /{slug}/. */
    private const PAGE_PREFIXES = ['tentang', 'layanan', 'program', 'ppid', 'sakip', 'survei', 'pengaduan'];

    public static function resolve(Request $request): ?string
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return null;
        }

        $path = trim(rawurldecode($request->getPathInfo()), '/');

        if ($path === '' || strlen($path) > 300 || str_contains($path, '..') || str_contains($path, '//')
            || ! preg_match('#^[\pL\pN/_.\-]+$#u', $path)) {
            return null;
        }

        // Feed RSS lama (/feed/, /category/x/feed/, /comments/feed/)
        if (preg_match('#^(?:(.+)/)?feed(?:/(?:rss2?|atom))?$#', $path, $m)) {
            $inner = $m[1] ?? '';

            return $inner === '' || $inner === 'comments' ? route('berita.index') : self::resolvePath($inner);
        }

        return self::resolvePath($path);
    }

    private static function resolvePath(string $path): ?string
    {
        // Panel admin WordPress lama
        if (preg_match('#^wp-admin(?:/.*)?$#', $path)) {
            return url('/admin/login');
        }

        // /category/induk/anak/ (+ /page/N/)
        if (preg_match('#^category/(.+?)(?:/page/(\d+))?$#', $path, $m)) {
            $slug = Str::afterLast($m[1], '/');
            $query = Category::where('slug', $slug)->exists() ? ['kategori' => $slug] : [];

            return self::newsIndex($query, $m[2] ?? null);
        }

        // /tag/nama-tag/ → pencarian berita dengan kata tag tersebut
        if (preg_match('#^tag/([^/]+)(?:/page/\d+)?$#', $path, $m)) {
            return route('search.index', ['q' => str_replace('-', ' ', $m[1])]);
        }

        // /author/nama-penulis/
        if (preg_match('#^author/([^/]+)(?:/page/\d+)?$#', $path, $m)) {
            $author = User::query()->pluck('name')
                ->first(fn ($name) => Str::slug($name) === Str::slug($m[1]));

            return $author ? route('penulis.show', ['username' => $author]) : route('berita.index');
        }

        // /page/N/ (halaman daftar tulisan)
        if (preg_match('#^page/(\d+)$#', $path, $m)) {
            return self::newsIndex([], $m[1]);
        }

        // Arsip tanggal: /2024/, /2024/05/, /2024/05/10/ (+ /page/N/)
        if (preg_match('#^(\d{4})(?:/(\d{2}))?(?:/\d{2})?(?:/page/(\d+))?$#', $path, $m)) {
            return self::newsIndex(array_filter([
                'tahun' => (int) $m[1],
                'bulan' => isset($m[2]) && $m[2] !== '' ? (int) $m[2] : null,
            ]), $m[3] ?? null);
        }

        // Tulisan bertanggal: /2024/05/10/judul-berita/ atau /2024/05/judul-berita/
        if (preg_match('#^\d{4}/\d{2}(?:/\d{2})?/([^/]+)$#', $path, $m)) {
            return self::newsBySlug($m[1]) ?? route('search.index', ['q' => str_replace('-', ' ', $m[1])]);
        }

        // /judul-berita/ (permalink "nama tulisan") atau halaman WordPress lama
        if (preg_match('#^[\pL\pN_\-]+$#u', $path)) {
            return self::newsBySlug($path) ?? self::staticPage($path);
        }

        return null;
    }

    private static function newsIndex(array $query, ?string $page): string
    {
        if ($page !== null && $page !== '' && (int) $page > 1) {
            $query['page'] = (int) $page;
        }

        return route('berita.index', $query);
    }

    private static function newsBySlug(string $slug): ?string
    {
        $slug = Str::lower($slug);

        return News::published()->where('slug', $slug)->exists()
            ? route('berita.show', ['slug' => $slug])
            : null;
    }

    /** /visi-misi/ → /tentang/visi-misi bila halaman baru dengan nama itu ada. */
    private static function staticPage(string $slug): ?string
    {
        $slug = Str::lower($slug);
        $getRoutes = Route::getRoutes()->get('GET');

        foreach (self::PAGE_PREFIXES as $prefix) {
            if (isset($getRoutes["{$prefix}/{$slug}"])) {
                return url("{$prefix}/{$slug}");
            }
        }

        return null;
    }
}
