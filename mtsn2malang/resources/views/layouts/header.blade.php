@php
    $siteName = \App\Models\Setting::get('site_name', 'MTsN 2 Kota Malang');
    $siteTagline = \App\Models\Setting::get('site_tagline', 'Madrasah Tsanawiyah Negeri 2');
    $logo = \App\Models\Setting::get('logo');
    $topHeaderAddress = \App\Models\Setting::get('top_header_address', 'Jl. Raya Cemorokandang No. 77 Kota Malang');
    $topHeaderMapsUrl = \App\Models\Setting::get('top_header_maps_url', 'https://maps.app.goo.gl/RHV4nKSg1eKe3qgo8');
    $topHeaderPhone = \App\Models\Setting::get('top_header_phone', '(0341) 711500');
    $topHeaderPhoneLink = \App\Models\Setting::get('top_header_phone_link', '0341711500');
    $topHeaderWaLabel   = \App\Models\Setting::get('top_header_wa_label', '0851');
    $topHeaderWaUrl     = \App\Models\Setting::get('top_header_wa_url', 'https://wa.me/6285163350000');
    $socialWaChannel    = \App\Models\Setting::get('social_whatsapp_channel', '');
    $topHeaderEmail = \App\Models\Setting::get('top_header_email', 'mtsnmalang2adm@gmail.com');

    $socialYoutube = \App\Models\Setting::get('social_youtube', 'https://www.youtube.com/channel/UCsqzV9zkTBlYZfmOA-_Y63A');
    $socialInstagram = \App\Models\Setting::get('social_instagram', 'https://www.instagram.com/mtsn2kota_malang');
    $socialFacebook = \App\Models\Setting::get('social_facebook', 'https://web.facebook.com/mtsn2kotamalang');
    $socialTiktok = \App\Models\Setting::get('social_tiktok', 'https://www.tiktok.com/@mtsn2kotamalang');
    $ppdbOnlineLink  = \App\Models\Registration::where('type', 'ppdb')->where('is_active', true)->value('registration_link');
    $linkSippn       = \App\Models\Setting::get('link_sippn', 'https://sippn.menpan.go.id/');
    $linkPtsp        = \App\Models\Setting::get('link_ptsp', 'https://ptsp.kemenag.go.id/');
    $linkSpanLapor   = \App\Models\Setting::get('link_span_lapor', 'https://www.lapor.go.id/');

    $availablePageKeys = \App\Models\PageContent::query()
        ->where(function ($query) {
            $query->where(function ($q) {
                    $q->whereNotNull('content')->where('content', '!=', '');
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('image')->where('image', '!=', '');
                })
                ->orWhere(function ($q) {
                    $q->whereNotNull('title')->where('title', '!=', '');
                });
        })
        ->pluck('page_key')
        ->flip();

    $hasPage = fn (string $key): bool => isset($availablePageKeys[$key]);
@endphp

<header id="site-header" class="site-header" x-data="{ mobileOpen: false, activeDropdown: null }">

    {{-- Top Bar --}}
    <div class="top-bar hidden md:block">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center gap-6 text-xs">
                <a href="{{ $topHeaderMapsUrl }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    {{ $topHeaderAddress }}
                </a>
                <a href="tel:{{ $topHeaderPhoneLink }}" class="flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    {{ $topHeaderPhone }}
                </a>
                <a href="{{ $topHeaderWaUrl }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WA PTSP: {{ $topHeaderWaLabel }}
                </a>
                <a href="mailto:{{ $topHeaderEmail }}" class="flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    {{ $topHeaderEmail }}
                </a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ $socialYoutube }}" target="_blank" rel="noopener" class="hover:text-red-400 transition-colors" aria-label="YouTube">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
                <a href="{{ $socialInstagram }}" target="_blank" rel="noopener" class="hover:text-pink-400 transition-colors" aria-label="Instagram">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
                <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" class="hover:text-blue-400 transition-colors" aria-label="Facebook">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                <a href="{{ $socialTiktok }}" target="_blank" rel="noopener" class="hover:text-gray-300 transition-colors" aria-label="TikTok">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                </a>
                @if($socialWaChannel)
                <a href="{{ $socialWaChannel }}" target="_blank" rel="noopener" class="hover:text-green-400 transition-colors" aria-label="WhatsApp Channel">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Announcement Ticker --}}
    @php
        $announcement = \App\Models\Setting::get('announcement_text', 'Selamat datang di website resmi MTsN 2 Kota Malang. Madrasah Tsanawiyah Negeri 2 Kota Malang - Unggul, Berkarakter, dan Berwawasan Global.');
        $announcementSpeed = (int) \App\Models\Setting::get('announcement_speed', 30);
        if ($announcementSpeed < 5 || $announcementSpeed > 120) {
            $announcementSpeed = 30;
        }
    @endphp
    @if($announcement)
    <div class="news-ticker py-1.5 px-4" style="--ticker-duration: {{ $announcementSpeed }}s;">
        <span class="ticker-label">Pengumuman</span>
        <div class="ticker-content px-4">
            <span class="ticker-text">{{ $announcement }}</span>
        </div>
    </div>
    @endif

    {{-- Main Navigation --}}
    <nav class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800" role="navigation" aria-label="Navigasi Utama">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 flex-shrink-0" aria-label="MTsN 2 Kota Malang - Beranda">
                    <div class="w-10 h-10 rounded-xl bg-white p-0.5 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="w-full h-full bg-white dark:bg-gray-900 rounded-[10px] overflow-hidden flex items-center justify-center">
                            @if($logo && file_exists(public_path('storage/' . $logo)))
                                <img src="/storage/{{ $logo }}" alt="{{ $siteName }}" class="w-full h-full object-cover">
                            @else
                                <svg viewBox="0 0 60 60" class="w-8 h-8" fill="none">
                                    <rect x="4" y="4" width="52" height="52" rx="12" fill="#1a7a3c"/>
                                    <text x="30" y="38" text-anchor="middle" fill="white" font-size="18" font-weight="bold" font-family="Poppins">M2</text>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-sm font-bold text-school-green leading-tight font-heading">{{ $siteName }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $siteTagline }}</div>
                    </div>
                </a>

                {{-- Desktop Menu --}}
                <div class="hidden lg:flex items-center gap-1">

                    <a href="{{ route('berita.index') }}" class="nav-link">Berita</a>

                    {{-- Tentang Kami --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            Tentang Kami
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            @if($hasPage('sambutan'))
                                <a href="{{ route('tentang.sambutan') }}" class="dropdown-item" role="menuitem">Sambutan</a>
                            @endif
                            @if($hasPage('selayang_pandang'))
                                <a href="{{ route('tentang.selayang-pandang') }}" class="dropdown-item" role="menuitem">Selayang Pandang</a>
                            @endif
                            @if($hasPage('visi_misi'))
                                <a href="{{ route('tentang.visi-misi') }}" class="dropdown-item" role="menuitem">Visi Misi dan Tujuan</a>
                            @endif
                            @if($hasPage('struktur_madrasah'))
                                <a href="{{ route('tentang.struktur-madrasah') }}" class="dropdown-item" role="menuitem">Struktur Madrasah</a>
                            @endif
                            @if($hasPage('struktur_komite'))
                                <a href="{{ route('tentang.struktur-komite') }}" class="dropdown-item" role="menuitem">Struktur Komite</a>
                            @endif
                            <a href="{{ route('tentang.daftar-gtk') }}" class="dropdown-item" role="menuitem">Daftar GTK</a>
                            @if($hasPage('ikatan_alumni'))
                                <a href="{{ route('tentang.ikatan-alumni') }}" class="dropdown-item" role="menuitem">Ikatan Alumni</a>
                            @endif
                            <a href="{{ route('tentang.ekstrakurikuler') }}" class="dropdown-item" role="menuitem">Ekstrakurikuler</a>
                            <a href="{{ route('tentang.sarpras') }}" class="dropdown-item" role="menuitem">Sarpras Madrasah</a>
                            <a href="{{ route('tentang.kontak') }}" class="dropdown-item" role="menuitem">Kontak</a>
                        </div>
                    </div>

                    {{-- Layanan --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            Layanan
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            @if($hasPage('maklumat'))
                                <a href="{{ route('layanan.maklumat') }}" class="dropdown-item" role="menuitem">Maklumat</a>
                            @endif
                            <a href="{{ route('layanan.standar-pelayanan') }}" class="dropdown-item" role="menuitem">Standar Pelayanan</a>
                            @if($hasPage('kompensasi'))
                                <a href="{{ route('layanan.kompensasi') }}" class="dropdown-item" role="menuitem">Kompensasi Pelayanan</a>
                            @endif
                            <a href="{{ route('layanan.ppdb') }}" class="dropdown-item" role="menuitem">Info PPDB</a>
                            <a href="{{ $ppdbOnlineLink ?: '#' }}" target="_blank" rel="noopener" class="dropdown-item flex items-center justify-between {{ $ppdbOnlineLink ? '' : 'opacity-50 pointer-events-none' }}" role="menuitem">
                                PPDB Online <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <a href="{{ $linkSippn }}" target="_blank" rel="noopener" class="dropdown-item flex items-center justify-between" role="menuitem">
                                SIPPN <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <a href="{{ $linkPtsp }}" target="_blank" rel="noopener" class="dropdown-item flex items-center justify-between" role="menuitem">
                                PTSP Online <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Program --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            Program
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            <a href="{{ route('program.unggulan') }}" class="dropdown-item" role="menuitem">Program Unggulan</a>
                            @if($hasPage('kelas_tahfidz'))
                                <a href="{{ route('program.tahfidz') }}" class="dropdown-item" role="menuitem">Kelas Tahfidz</a>
                            @endif
                            @if($hasPage('kelas_olimpiade'))
                                <a href="{{ route('program.olimpiade') }}" class="dropdown-item" role="menuitem">Kelas Olimpiade</a>
                            @endif
                            @if($hasPage('kelas_bilingual'))
                                <a href="{{ route('program.bilingual') }}" class="dropdown-item" role="menuitem">Kelas Bilingual</a>
                            @endif
                            @if($hasPage('kelas_cbi'))
                                <a href="{{ route('program.cbi') }}" class="dropdown-item" role="menuitem">Kelas CBI</a>
                            @endif
                            @if($hasPage('adiwiyata'))
                                <a href="{{ route('program.adiwiyata') }}" class="dropdown-item" role="menuitem">Adiwiyata</a>
                            @endif
                            <a href="{{ route('program.zona-integritas') }}" class="dropdown-item" role="menuitem">Zona Integritas</a>
                        </div>
                    </div>

                    {{-- PPID --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            PPID
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            <a href="{{ route('ppid.profil') }}" class="dropdown-item" role="menuitem">Profil PPID</a>
                            <a href="{{ route('ppid.berkala') }}" class="dropdown-item" role="menuitem">Informasi Berkala</a>
                            <a href="{{ route('ppid.setiap-saat') }}" class="dropdown-item" role="menuitem">Informasi Setiap Saat</a>
                            <a href="{{ route('ppid.serta-merta') }}" class="dropdown-item" role="menuitem">Informasi Serta Merta</a>
                            <a href="{{ route('ppid.galeri') }}" class="dropdown-item" role="menuitem">Galeri Foto</a>
                        </div>
                    </div>

                    {{-- SAKIP --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            SAKIP
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            <a href="{{ route('sakip.renstra') }}" class="dropdown-item" role="menuitem">RENSTRA</a>
                            <a href="{{ route('sakip.perkin') }}" class="dropdown-item" role="menuitem">PERKIN</a>
                            <a href="{{ route('sakip.lkj') }}" class="dropdown-item" role="menuitem">LKJ</a>
                        </div>
                    </div>

                    {{-- Ma'had --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            Ma'had
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            <a href="{{ route('mahad.home') }}" class="dropdown-item" role="menuitem">Website Mahad</a>
                            <a href="{{ route('mahad.berita') }}" class="dropdown-item" role="menuitem">Berita</a>
                            <a href="{{ route('mahad.psb') }}" class="dropdown-item" role="menuitem">PSB</a>
                            <a href="{{ route('mahad.program') }}" class="dropdown-item" role="menuitem">Program</a>
                            <a href="{{ route('mahad.sarpras') }}" class="dropdown-item" role="menuitem">Sarpras Ma'had</a>
                            @if($hasPage('struktur_mahad'))
                                <a href="{{ route('mahad.struktur') }}" class="dropdown-item" role="menuitem">Struktur Ma'had</a>
                            @endif
                            <a href="{{ route('mahad.kontak') }}" class="dropdown-item" role="menuitem">Kontak Ma'had</a>
                        </div>
                    </div>

                    {{-- Pengaduan --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            Pengaduan
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            <a href="{{ route('pengaduan.whistleblowing') }}" class="dropdown-item" role="menuitem">Whistleblowing</a>
                            <a href="{{ route('pengaduan.masyarakat') }}" class="dropdown-item" role="menuitem">Pengaduan Masyarakat</a>
                            <a href="{{ route('pengaduan.pelacakan') }}" class="dropdown-item" role="menuitem">Pelacakan Nomor Pengaduan</a>
                            <a href="{{ route('pengaduan.laporan') }}" class="dropdown-item" role="menuitem">Laporan Hasil</a>
                            <a href="{{ $linkSpanLapor }}" target="_blank" rel="noopener" class="dropdown-item flex items-center justify-between" role="menuitem">
                                SPAN Lapor <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Survei --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="nav-link flex items-center gap-1" aria-haspopup="true" :aria-expanded="open">
                            Survei
                            <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition class="dropdown-menu" role="menu">
                            <a href="{{ route('survei.form') }}" class="dropdown-item" role="menuitem">SKM & SPK</a>
                            <a href="{{ route('survei.hasil') }}" class="dropdown-item" role="menuitem">Hasil Survei</a>
                            <a href="{{ route('survei.saran') }}" class="dropdown-item" role="menuitem">Saran</a>
                        </div>
                    </div>
                </div>

                {{-- Right Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Search --}}
                    <button onclick="toggleSearch()" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="Cari" title="Cari (Ctrl+K)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    {{-- Dark Mode Toggle --}}
                    <button x-data="{ theme: localStorage.getItem('theme') || 'system' }"
                        @click="theme = themeManager.toggle(); $el.setAttribute('title', 'Mode: ' + theme)"
                        class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        aria-label="Toggle mode gelap/terang" title="Mode Tampilan">
                        <svg x-show="theme !== 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    {{-- Login Button --}}
                    @auth
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-school-green text-white text-sm font-semibold rounded-lg hover:bg-school-green-dark transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-school-green text-white text-sm font-semibold rounded-lg hover:bg-school-green-dark transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            Login
                        </a>
                    @endauth

                    {{-- Mobile Menu Toggle --}}
                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="Menu" :aria-expanded="mobileOpen">
                        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-collapse class="lg:hidden bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 overflow-y-auto max-h-[80vh]">
            <div class="container mx-auto px-4 py-4 space-y-1">
                <a href="{{ route('berita.index') }}" class="block px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-school-green rounded-lg transition-colors">Berita</a>

                @php
                $mobileMenus = [
                    'Tentang Kami' => array_values(array_filter([
                        $hasPage('sambutan') ? ['Sambutan', route('tentang.sambutan')] : null,
                        $hasPage('selayang_pandang') ? ['Selayang Pandang', route('tentang.selayang-pandang')] : null,
                        $hasPage('visi_misi') ? ['Visi Misi dan Tujuan', route('tentang.visi-misi')] : null,
                        $hasPage('struktur_madrasah') ? ['Struktur Madrasah', route('tentang.struktur-madrasah')] : null,
                        $hasPage('struktur_komite') ? ['Struktur Komite', route('tentang.struktur-komite')] : null,
                        ['Daftar GTK', route('tentang.daftar-gtk')],
                        $hasPage('ikatan_alumni') ? ['Ikatan Alumni', route('tentang.ikatan-alumni')] : null,
                        ['Ekstrakurikuler', route('tentang.ekstrakurikuler')],
                        ['Sarpras Madrasah', route('tentang.sarpras')],
                        ['Kontak', route('tentang.kontak')],
                    ])),
                    'Layanan' => array_values(array_filter([
                        $hasPage('maklumat') ? ['Maklumat', route('layanan.maklumat')] : null,
                        ['Standar Pelayanan', route('layanan.standar-pelayanan')],
                        $hasPage('kompensasi') ? ['Kompensasi Pelayanan', route('layanan.kompensasi')] : null,
                        ['Info PPDB', route('layanan.ppdb')],
                        ['PPDB Online', $ppdbOnlineLink ?: '#'],
                        ['SIPPN', $linkSippn],
                        ['PTSP Online', $linkPtsp],
                    ])),
                    'Program' => array_values(array_filter([
                        ['Program Unggulan', route('program.unggulan')],
                        $hasPage('kelas_tahfidz') ? ['Kelas Tahfidz', route('program.tahfidz')] : null,
                        $hasPage('kelas_olimpiade') ? ['Kelas Olimpiade', route('program.olimpiade')] : null,
                        $hasPage('kelas_bilingual') ? ['Kelas Bilingual', route('program.bilingual')] : null,
                        $hasPage('kelas_cbi') ? ['Kelas CBI', route('program.cbi')] : null,
                        $hasPage('adiwiyata') ? ['Adiwiyata', route('program.adiwiyata')] : null,
                        ['Zona Integritas', route('program.zona-integritas')],
                    ])),
                    'PPID' => [
                        ['Profil PPID', route('ppid.profil')],
                        ['Informasi Berkala', route('ppid.berkala')],
                        ['Informasi Setiap Saat', route('ppid.setiap-saat')],
                        ['Informasi Serta Merta', route('ppid.serta-merta')],
                        ['Galeri Foto', route('ppid.galeri')],
                    ],
                    'SAKIP' => [
                        ['RENSTRA', route('sakip.renstra')],
                        ['PERKIN', route('sakip.perkin')],
                        ['LKJ', route('sakip.lkj')],
                    ],
                    "Ma'had" => array_values(array_filter([
                        ['Website Mahad', route('mahad.home')],
                        ['Berita', route('mahad.berita')],
                        ['PSB', route('mahad.psb')],
                        ['Program', route('mahad.program')],
                        ["Sarpras Ma'had", route('mahad.sarpras')],
                        $hasPage('struktur_mahad') ? ["Struktur Ma'had", route('mahad.struktur')] : null,
                        ["Kontak Ma'had", route('mahad.kontak')],
                    ])),
                    'Pengaduan' => [
                        ['Whistleblowing', route('pengaduan.whistleblowing')],
                        ['Pengaduan Masyarakat', route('pengaduan.masyarakat')],
                        ['Pelacakan Nomor Pengaduan', route('pengaduan.pelacakan')],
                        ['Laporan Hasil', route('pengaduan.laporan')],
                        ['SPAN Lapor', $linkSpanLapor],
                    ],
                    'Survei' => [
                        ['SKM & SPK', route('survei.form')],
                        ['Hasil Survei', route('survei.hasil')],
                        ['Saran', route('survei.saran')],
                    ],
                ];
                @endphp

                @foreach($mobileMenus as $label => $items)
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-school-green rounded-lg transition-colors">
                        {{ $label }}
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-collapse class="pl-4 space-y-1 mt-1">
                        @foreach($items as $item)
                        <a href="{{ $item[1] }}" class="block px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-school-green hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors">{{ $item[0] }}</a>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                    @auth
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="flex items-center gap-2 px-4 py-3 bg-school-green text-white text-sm font-semibold rounded-lg">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-2 px-4 py-3 bg-school-green text-white text-sm font-semibold rounded-lg">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
