<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use App\Models\Setting;
use App\Services\MadrasahProfileService;
use App\Services\PortalDataService;
use App\Support\PortalSettings;

/**
 * Halaman Portal — landing page di "/" (kop, running text, popup terjadwal,
 * Tab Tentang & Tab Portal).
 *
 * Sumber data: running text = komponen x-announcement-ticker (sama dengan
 * ticker header); Tab Tentang = MadrasahProfileService (Setting profil madrasah);
 * Tab Portal = PortalDataService (tabel portal_quick_access,
 * portal_categories, portal_links — hanya yang aktif). Kop = Setting identitas situs (kopData()). Gambar latar
 * = PortalSettings::background() (Portal → Pengaturan Portal).
 */
class PortalController extends Controller
{
    public function index(MadrasahProfileService $profile, PortalDataService $portal)
    {
        // Portal dimatikan admin → "/" langsung menampilkan Beranda (tanpa
        // redirect); data Portal tetap tersimpan.
        if (! PortalSettings::enabled()) {
            return app(HomeController::class)->index();
        }

        PageView::record('/');

        return view('pages.portal.index', [
            'kop'         => $this->kopData(),
            'tentang'     => $profile->forTentangTab(),
            'quickAccess' => $portal->activeQuickAccess(),
            'categories'  => $portal->activeCategories(),
            'background'  => PortalSettings::background(),
        ]);
    }

    /**
     * Kop Portal — Setting yang sama dengan header situs (Pengaturan Website →
     * Umum & Header), dengan nilai bawaan yang sama.
     */
    private function kopData(): array
    {
        $logo = Setting::get('logo');
        $phoneLink = Setting::get('top_header_phone_link', '0341711500');

        return [
            'name'          => Setting::get('site_name', 'MTsN 2 Kota Malang'),
            'tagline'       => Setting::get('site_tagline', 'Madrasah Tsanawiyah Negeri 2'),
            'logo'          => filled($logo) ? asset('storage/' . $logo) : null,
            'address'       => Setting::get('top_header_address', 'Jl. Raya Cemorokandang No. 77 Kota Malang'),
            'mapsUrl'       => Setting::get('top_header_maps_url'),
            'phone'         => Setting::get('top_header_phone', '(0341) 711500'),
            'phoneUrl'      => filled($phoneLink) ? 'tel:' . $phoneLink : null,
            'whatsapp'      => Setting::get('top_header_wa_url', 'https://wa.me/6285163350000'),
            'whatsappLabel' => 'WA PTSP: ' . Setting::get('top_header_wa_label', '0851'),
            'email'         => Setting::get('top_header_email', 'mtsnmalang2adm@gmail.com'),
        ];
    }
}
