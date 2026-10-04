<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('theme') || 'system' }" x-init="$watch('darkMode', val => { localStorage.setItem('theme', val); document.documentElement.classList.toggle('dark', val === 'dark' || (val === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)); })">
<head>
    <meta charset="utf-8">
    {{-- Apply dark class before render to avoid FOUC --}}
    <script>
        (function(){var t=localStorage.getItem('theme');if(t==='dark'||(t!=='light'&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}})();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <title>@yield('title', config('app.name', 'MTsN 2 Kota Malang'))</title>
    <meta name="description" content="@yield('description', 'Website resmi MTsN 2 Kota Malang - Madrasah Tsanawiyah Negeri 2 Kota Malang. Informasi berita, akademik, dan layanan pendidikan.')">
    <meta name="keywords" content="@yield('keywords', 'MTsN 2 Kota Malang, Madrasah, Pendidikan, Berita Sekolah, Malang')">
    <meta name="author" content="MTsN 2 Kota Malang">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Social Media --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('og_description', 'Website resmi MTsN 2 Kota Malang')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="MTsN 2 Kota Malang">
    <meta property="og:locale" content="id_ID">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', config('app.name'))">
    <meta name="twitter:description" content="@yield('og_description', 'Website resmi MTsN 2 Kota Malang')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

    {{-- WhatsApp --}}
    <meta property="og:image:type" content="image/jpeg">

    @php
        // Favicon: gunakan URL relatif agar tidak bergantung pada APP_URL
        $dynamicFavicon = \App\Models\Setting::get('favicon');
        if ($dynamicFavicon && file_exists(public_path('storage/' . $dynamicFavicon))) {
            $faviconRelPath = 'storage/' . $dynamicFavicon;
            $faviconExt = strtolower(pathinfo($dynamicFavicon, PATHINFO_EXTENSION));
        } else {
            // Fallback ke favicon.ico statis yang sudah pasti ada
            $faviconRelPath = file_exists(public_path('images/favicon.ico'))
                ? 'images/favicon.ico'
                : 'images/favicon.png';
            $faviconExt = pathinfo($faviconRelPath, PATHINFO_EXTENSION);
        }
        $faviconVersion = file_exists(public_path($faviconRelPath)) ? filemtime(public_path($faviconRelPath)) : time();
        $faviconUrl = '/' . $faviconRelPath;

        $faviconMime = match($faviconExt) {
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'webp' => 'image/webp',
            default => 'image/x-icon',
        };

        // Logo: gunakan URL relatif agar tidak bergantung pada APP_URL
        $dynamicLogo = \App\Models\Setting::get('logo');
        if ($dynamicLogo && file_exists(public_path('storage/' . $dynamicLogo))) {
            $logoUrl = '/storage/' . $dynamicLogo;
        } else {
            $logoUrl = null;
        }
    @endphp

    {{-- Favicon: ICO statis sebagai fallback universal, lalu format dinamis --}}
    <link rel="icon" href="/images/favicon.ico" sizes="any">
    <link rel="icon" type="{{ $faviconMime }}" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
    <link rel="shortcut icon" type="{{ $faviconMime }}" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
    <link rel="apple-touch-icon" href="{{ $dynamicFavicon && $faviconExt !== 'ico' ? $faviconUrl . '?v=' . $faviconVersion : '/images/apple-touch-icon.png' }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    {{-- App CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    {{-- Schema.org --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "EducationalOrganization",
        "name": "MTsN 2 Kota Malang",
        "url": "{{ config('app.url') }}",
        "logo": "{{ $logoUrl }}",
        "address": {
            "@@type": "PostalAddress",
            "streetAddress": "Jl. Raya Cemorokandang No. 77",
            "addressLocality": "Kota Malang",
            "postalCode": "65138",
            "addressCountry": "ID"
        },
        "telephone": "+62341711500",
        "email": "mtsnmalang2adm@gmail.com"
    }
    </script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300" id="top">

    {{-- Loading Screen --}}
    <div id="loading-screen" class="loading-screen">
        <div class="loading-logo">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white p-1 shadow-sm border border-gray-100">
                    <div class="w-full h-full bg-white rounded-[14px] overflow-hidden flex items-center justify-center">
                        @if($dynamicLogo)
                            <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::get('site_name', 'MTsN 2 Kota Malang') }}" class="w-full h-full object-cover">
                        @else
                            <svg viewBox="0 0 60 60" class="w-12 h-12" fill="none">
                                <rect x="4" y="4" width="52" height="52" rx="12" fill="#1a7a3c"/>
                                <text x="30" y="38" text-anchor="middle" fill="white" font-size="20" font-weight="bold" font-family="Poppins">M2</text>
                            </svg>
                        @endif
                    </div>
                </div>
                <div class="text-white">
                    <div class="text-xl font-bold font-heading">{{ \App\Models\Setting::get('site_name', 'MTsN 2 Kota Malang') }}</div>
                    <div class="text-sm opacity-80">{{ \App\Models\Setting::get('site_tagline', 'Kota Malang') }}</div>
                </div>
            </div>
            <div class="loading-bar mt-4">
                <div class="loading-bar-fill"></div>
            </div>
            <p class="text-white/70 text-sm mt-2">Memuat halaman...</p>
        </div>
    </div>

    {{-- Accessibility Widget --}}
    @include('components.accessibility')

    {{-- Search Overlay --}}
    @include('components.search-overlay')

    {{-- Header — halaman dengan kop sendiri (mis. Portal) bisa menyembunyikannya
         lewat @section('hide_site_header', true) --}}
    @unless(View::hasSection('hide_site_header'))
        @include('layouts.header')
    @endunless

    {{-- Main Content --}}
    <main id="main-content" role="main">
        @yield('content')
    </main>

    {{-- Footer — halaman dengan footer sendiri (mis. Portal) bisa
         menyembunyikannya lewat @section('hide_site_footer', true) --}}
    @unless(View::hasSection('hide_site_footer'))
        @include('layouts.footer')
    @endunless

    {{-- Back to Top --}}
    <button id="back-to-top" class="back-to-top" aria-label="Kembali ke atas" title="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    {{-- Popup & Pengumuman --}}
    @include('components.popup')

    @stack('scripts')
</body>
</html>
