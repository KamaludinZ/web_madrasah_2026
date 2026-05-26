<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('theme') || 'system' }" :class="{ 'dark': darkMode === 'dark' || (darkMode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) }">
<head>
    <meta charset="utf-8">
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

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    {{-- AOS CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">

    {{-- GLightbox CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

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
        "logo": "{{ asset('images/logo.png') }}",
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
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-2xl">
                    <svg viewBox="0 0 60 60" class="w-12 h-12" fill="none">
                        <circle cx="30" cy="30" r="28" fill="#1a7a3c"/>
                        <text x="30" y="38" text-anchor="middle" fill="white" font-size="20" font-weight="bold" font-family="Poppins">M2</text>
                    </svg>
                </div>
                <div class="text-white">
                    <div class="text-xl font-bold font-heading">MTsN 2</div>
                    <div class="text-sm opacity-80">Kota Malang</div>
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

    {{-- Header --}}
    @include('layouts.header')

    {{-- Main Content --}}
    <main id="main-content" role="main">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('layouts.footer')

    {{-- Back to Top --}}
    <button id="back-to-top" class="back-to-top" aria-label="Kembali ke atas" title="Kembali ke atas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    @stack('scripts')
</body>
</html>
