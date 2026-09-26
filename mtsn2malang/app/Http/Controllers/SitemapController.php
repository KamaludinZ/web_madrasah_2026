<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /** Halaman publik statis (nama route) yang dimasukkan ke sitemap. */
    private const STATIC_ROUTES = [
        'home', 'berita.index',
        'tentang.sambutan', 'tentang.selayang-pandang', 'tentang.visi-misi', 'tentang.struktur-madrasah',
        'tentang.struktur-komite', 'tentang.daftar-gtk', 'tentang.ikatan-alumni', 'tentang.ekstrakurikuler',
        'tentang.sarpras', 'tentang.kontak',
        'layanan.maklumat', 'layanan.standar-pelayanan', 'layanan.kompensasi', 'layanan.ppdb',
        'program.unggulan', 'program.tahfidz', 'program.olimpiade', 'program.bilingual', 'program.cbi',
        'program.adiwiyata', 'program.zona-integritas',
        'ppid.profil', 'ppid.berkala', 'ppid.setiap-saat', 'ppid.serta-merta', 'ppid.galeri',
        'sakip.renstra', 'sakip.perkin', 'sakip.lkj',
        'mahad.home', 'mahad.berita', 'mahad.psb', 'mahad.program', 'mahad.sarpras', 'mahad.struktur', 'mahad.kontak',
        'pengaduan.whistleblowing', 'pengaduan.masyarakat', 'pengaduan.laporan',
        'survei.form', 'survei.hasil', 'survei.saran',
    ];

    public function index()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $sitemap = Sitemap::create();

            foreach (self::STATIC_ROUTES as $name) {
                if (Route::has($name)) {
                    $sitemap->add(Url::create(route($name)));
                }
            }

            News::published()
                ->latest('published_at')
                ->select(['slug', 'updated_at', 'published_at'])
                ->limit(5000)
                ->get()
                ->each(fn (News $news) => $sitemap->add(
                    Url::create(route('berita.show', $news->slug))
                        ->setLastModificationDate($news->updated_at ?? $news->published_at)
                ));

            return $sitemap->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
