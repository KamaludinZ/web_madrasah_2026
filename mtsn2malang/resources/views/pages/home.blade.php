@extends('layouts.app')

@section('title', 'MTsN 2 Kota Malang - Madrasah Tsanawiyah Negeri 2 Kota Malang')
@section('description', 'Website resmi MTsN 2 Kota Malang. Madrasah Tsanawiyah Negeri 2 Kota Malang - Unggul, Berkarakter, dan Berwawasan Global.')

@section('content')

{{-- Hero Slider --}}
<section class="hero-slider" aria-label="Banner Utama">
    <div class="hero-swiper swiper h-full">
        <div class="swiper-wrapper">
            @forelse($banners as $banner)
            <div class="swiper-slide">
                <div class="hero-slide">
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover hero-zoom-image">
                    <div class="hero-overlay"></div>
                    <div class="absolute inset-0 flex items-center">
                        <div class="container mx-auto px-4 md:px-8">
                            <div class="max-w-2xl" data-aos="fade-right">
                                @if($banner->subtitle)
                                <span class="inline-block px-3 py-1 bg-orange-700 text-white text-sm font-semibold rounded-full mb-4">{{ $banner->subtitle }}</span>
                                @endif
                                <h1 class="text-3xl md:text-5xl font-bold text-white font-heading leading-tight mb-4">{{ $banner->title }}</h1>
                                @if($banner->description)
                                <p class="text-white/80 text-lg mb-6">{{ $banner->description }}</p>
                                @endif
                                @if($banner->link)
                                <a href="{{ $banner->link }}" class="btn-secondary">
                                    Selengkapnya
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="swiper-slide">
                @if(!empty($defaultBannerImage))
                <div class="hero-slide">
                    <img src="/storage/{{ $defaultBannerImage }}" alt="Slide Default MTsN 2 Kota Malang" class="w-full h-full object-cover hero-zoom-image">
                    <div class="hero-overlay"></div>
                    <div class="absolute inset-0 flex items-center">
                        <div class="container mx-auto px-4 md:px-8 text-center">
                            @if(!empty($defaultBannerSubtitle))
                            <span class="inline-block px-3 py-1 bg-orange-700 text-white text-sm font-semibold rounded-full mb-4">{{ $defaultBannerSubtitle }}</span>
                            @endif
                            <h1 class="text-4xl md:text-6xl font-bold text-white font-heading mb-4 [&_*]:text-white [&_a]:underline">{!! \App\Support\SafeHtml::clean($defaultBannerTitle) !!}</h1>
                            @if(!empty($defaultBannerDescription))
                            <div class="text-white/80 text-xl mb-8 [&_*]:text-white/80 [&_a]:underline">{!! \App\Support\SafeHtml::clean($defaultBannerDescription) !!}</div>
                            @endif
                            <a href="{{ $defaultBannerLink ?: route('tentang.sambutan') }}" class="btn-secondary">Pelajari Lebih Lanjut</a>
                        </div>
                    </div>
                </div>
                @else
                <div class="hero-slide bg-gradient-to-br from-school-green-dark via-school-green to-school-orange">
                    <div class="absolute inset-0 flex items-center">
                        <div class="container mx-auto px-4 md:px-8 text-center">
                            @if(!empty($defaultBannerSubtitle))
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-sm font-semibold rounded-full mb-4">{{ $defaultBannerSubtitle }}</span>
                            @endif
                            <h1 class="text-4xl md:text-6xl font-bold text-white font-heading mb-4 [&_*]:text-white [&_a]:underline">{!! \App\Support\SafeHtml::clean($defaultBannerTitle) !!}</h1>
                            @if(!empty($defaultBannerDescription))
                            <div class="text-white/80 text-xl mb-8 [&_*]:text-white/80 [&_a]:underline">{!! \App\Support\SafeHtml::clean($defaultBannerDescription) !!}</div>
                            @endif
                            <a href="{{ $defaultBannerLink ?: route('tentang.sambutan') }}" class="btn-secondary">Pelajari Lebih Lanjut</a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforelse
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev hero-nav-btn hero-nav-prev !w-11 !h-11 !rounded-full after:!hidden">
            <span class="sr-only">Sebelumnya</span>
            <svg aria-hidden="true" viewBox="0 0 24 24" class="w-5 h-5">
                <path d="M15 6L9 12L15 18" />
            </svg>
        </div>
        <div class="swiper-button-next hero-nav-btn hero-nav-next !w-11 !h-11 !rounded-full after:!hidden">
            <span class="sr-only">Berikutnya</span>
            <svg aria-hidden="true" viewBox="0 0 24 24" class="w-5 h-5">
                <path d="M9 6L15 12L9 18" />
            </svg>
        </div>
    </div>
</section>

{{-- Quick Service Buttons --}}
<section class="py-6 bg-white dark:bg-gray-900 shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('layanan.standar-pelayanan') }}" class="flex items-center gap-2 px-5 py-3 bg-school-green text-white rounded-xl font-semibold text-sm hover:bg-school-green-dark transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Layanan
            </a>
            <a href="{{ route('survei.form') }}" class="flex items-center gap-2 px-5 py-3 bg-orange-700 text-white rounded-xl font-semibold text-sm hover:bg-orange-800 transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Survei
            </a>
            <a href="{{ route('pengaduan.masyarakat') }}" class="flex items-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-xl font-semibold text-sm hover:bg-blue-700 transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Pengaduan
            </a>
            <a href="https://ptsp.kemenag.go.id/" target="_blank" rel="noopener" class="flex items-center gap-2 px-5 py-3 bg-purple-600 text-white rounded-xl font-semibold text-sm hover:bg-purple-700 transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                PTSP Online
            </a>
            <a href="{{ route('mahad.home') }}" class="flex items-center gap-2 px-5 py-3 bg-teal-600 text-white rounded-xl font-semibold text-sm hover:bg-teal-700 transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4 8 4v14M9 9h8M9 13h8M9 17h8"/></svg>
                Website Ma'had
            </a>
        </div>
    </div>
</section>

{{-- ===== SECTION SAMBUTAN & PROFIL ===== --}}
@php
    $statStudents = \App\Models\Setting::get('stat_students');
    $statTeachers = \App\Models\Setting::get('stat_teachers');
    $statStaff    = \App\Models\Setting::get('stat_staff');
    $statAlumni   = \App\Models\Setting::get('stat_alumni');

    $welcomePhoto   = \App\Models\Setting::get('welcome_headmaster_photo');
    $welcomeName    = \App\Models\Setting::get('welcome_headmaster_name');
    $welcomePeriod  = \App\Models\Setting::get('welcome_headmaster_period');
    $welcomeMessage = \App\Models\Setting::get('welcome_headmaster_message');
    $welcomeMotto   = \App\Models\Setting::get('welcome_headmaster_motto');

    $visi   = \App\Models\Setting::get('visi');
    $misi   = \App\Models\Setting::get('misi', '');
    $tujuan = \App\Models\Setting::get('tujuan', '');

    $identityNomenklatur   = \App\Models\Setting::get('identity_nomenklatur');
    $identityNpsn          = \App\Models\Setting::get('identity_npsn');
    $identityNsm           = \App\Models\Setting::get('identity_nsm');
    $identityAkreditasi    = \App\Models\Setting::get('identity_akreditasi');
    $identityAddress       = \App\Models\Setting::get('identity_address');
    $identityWebsite       = \App\Models\Setting::get('identity_website');
    $identityAdiwiyata     = \App\Models\Setting::get('identity_adiwiyata');
    $identityZI            = \App\Models\Setting::get('identity_zona_integritas');
    $identityNpsnUrl       = \App\Models\Setting::get('identity_npsn_url');
    $identityNsmUrl        = \App\Models\Setting::get('identity_nsm_url');

    $hasStats   = $statStudents || $statTeachers || $statStaff || $statAlumni;
    $hasProfile = $welcomeName || $visi || $misi || $identityNomenklatur;
@endphp

@if($hasStats || $hasProfile)
<section class="py-10 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">

        {{-- ── Data Akademika (Stats Row) ── --}}
        @if($hasStats)
        @php
            // Helper: format angka jika numerik murni, tampilkan apa adanya jika ada karakter non-angka (misal: "5000+")
            $fmtStat = fn($v) => is_numeric(str_replace([',','.'], '', $v)) && !preg_match('/[^0-9.,]/', $v)
                ? number_format((float) str_replace(',', '', $v))
                : $v;
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10" data-aos="fade-up">
            @if($statStudents)
            <div class="text-center p-5 rounded-2xl bg-gradient-to-br from-school-green to-school-green-dark text-white shadow-md">
                <div class="text-3xl font-bold font-heading" data-counter="{{ $statStudents }}">{{ $fmtStat($statStudents) }}</div>
                <div class="text-sm text-green-100 mt-1">Siswa</div>
            </div>
            @endif
            @if($statTeachers)
            <div class="text-center p-5 rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 text-white shadow-md">
                <div class="text-3xl font-bold font-heading" data-counter="{{ $statTeachers }}">{{ $fmtStat($statTeachers) }}</div>
                <div class="text-sm text-blue-100 mt-1">Guru</div>
            </div>
            @endif
            @if($statStaff)
            <div class="text-center p-5 rounded-2xl bg-gradient-to-br from-orange-700 to-orange-900 text-white shadow-md">
                <div class="text-3xl font-bold font-heading" data-counter="{{ $statStaff }}">{{ $fmtStat($statStaff) }}</div>
                <div class="text-sm text-orange-100 mt-1">Tenaga Kependidikan</div>
            </div>
            @endif
            @if($statAlumni)
            <div class="text-center p-5 rounded-2xl bg-gradient-to-br from-purple-700 to-purple-900 text-white shadow-md">
                <div class="text-3xl font-bold font-heading" data-counter="{{ $statAlumni }}">{{ $fmtStat($statAlumni) }}</div>
                <div class="text-sm text-purple-100 mt-1">Alumni</div>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Tab Profil (Sambutan / Visi / Misi / Tujuan / Identitas) ── --}}
        @if($hasProfile)
        <div x-data="{ tab: 'sambutan' }" data-aos="fade-up">
            {{-- Tab Nav --}}
            <div class="flex flex-wrap gap-1 border-b border-gray-200 dark:border-gray-700 mb-6 overflow-x-auto">
                @if($welcomeName || $welcomeMessage)
                <button @click="tab='sambutan'" :class="tab==='sambutan' ? 'border-school-green text-school-green bg-green-50 dark:bg-green-900/20' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all whitespace-nowrap rounded-t-lg">
                    Sambutan Kepala
                </button>
                @endif
                @if($visi)
                <button @click="tab='visi'" :class="tab==='visi' ? 'border-school-green text-school-green bg-green-50 dark:bg-green-900/20' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all whitespace-nowrap rounded-t-lg">
                    Visi
                </button>
                @endif
                @if($misi)
                <button @click="tab='misi'" :class="tab==='misi' ? 'border-school-green text-school-green bg-green-50 dark:bg-green-900/20' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all whitespace-nowrap rounded-t-lg">
                    Misi
                </button>
                @endif
                @if($tujuan)
                <button @click="tab='tujuan'" :class="tab==='tujuan' ? 'border-school-green text-school-green bg-green-50 dark:bg-green-900/20' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all whitespace-nowrap rounded-t-lg">
                    Tujuan
                </button>
                @endif
                @if($identityNomenklatur || $identityNpsn)
                <button @click="tab='identitas'" :class="tab==='identitas' ? 'border-school-green text-school-green bg-green-50 dark:bg-green-900/20' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-all whitespace-nowrap rounded-t-lg">
                    Identitas
                </button>
                @endif
            </div>

            {{-- Tab: Sambutan --}}
            @if($welcomeName || $welcomeMessage)
            <div x-show="tab==='sambutan'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex flex-col md:flex-row gap-8 items-start">

                    {{-- Foto kepala: rasio 1:1, tanpa border --}}
                    @if($welcomePhoto)
                    <div class="flex-shrink-0 mx-auto md:mx-0 w-72 h-72">
                        <img src="{{ asset('storage/' . $welcomePhoto) }}" alt="{{ $welcomeName }}"
                             class="w-full h-full object-cover rounded-2xl shadow-xl">
                    </div>
                    @endif

                    {{-- Konten: sambutan → motto → nama & periode --}}
                    <div class="flex-1 min-w-0">
                        @if($welcomeMessage)
                        <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
                            {!! \App\Support\SafeHtml::clean($welcomeMessage) !!}
                        </div>
                        @endif

                        @if($welcomeMotto)
                        <blockquote class="mt-5 border-l-4 border-school-green pl-4 italic text-school-green dark:text-green-400 font-medium">
                            {{ $welcomeMotto }}
                        </blockquote>
                        @endif

                        {{-- Nama & periode di bawah motto, tanpa foto --}}
                        @if($welcomeName || $welcomePeriod)
                        <div class="mt-4">
                            @if($welcomeName)
                            <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $welcomeName }}</p>
                            @endif
                            @if($welcomePeriod)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $welcomePeriod }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Tab: Visi --}}
            @if($visi)
            <div x-show="tab==='visi'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="max-w-3xl mx-auto py-2">
                    <div class="flex items-start gap-5 p-6
                                border border-gray-200 dark:border-gray-700
                                rounded-2xl">
                        {{-- Ikon kiri --}}
                        <div class="flex-shrink-0 w-12 h-12
                                    rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <p class="text-lg md:text-xl text-gray-800 dark:text-gray-200 font-semibold leading-relaxed">
                            {{ $visi }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tab: Misi --}}
            @if($misi)
            <div x-show="tab==='misi'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="max-w-3xl mx-auto">
                    <div class="flex items-start gap-5 p-6
                                border border-gray-200 dark:border-gray-700
                                rounded-2xl">
                        {{-- Ikon kiri --}}
                        <div class="flex-shrink-0 w-12 h-12
                                    rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        {{-- Konten --}}
                        <div class="flex-1 min-w-0 prose prose-sm dark:prose-invert max-w-none
                                    [&_ol]:list-decimal [&_ul]:list-disc
                                    [&_ol]:pl-5 [&_ul]:pl-5
                                    [&_li]:text-gray-700 dark:[&_li]:text-gray-300 [&_li]:leading-relaxed [&_li]:mb-1.5
                                    [&_p]:text-gray-700 dark:[&_p]:text-gray-300">
                            {!! \App\Support\SafeHtml::clean($misi) !!}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tab: Tujuan --}}
            @if($tujuan)
            <div x-show="tab==='tujuan'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="max-w-3xl mx-auto">
                    <div class="flex items-start gap-5 p-6
                                border border-gray-200 dark:border-gray-700
                                rounded-2xl">
                        {{-- Ikon kiri --}}
                        <div class="flex-shrink-0 w-12 h-12
                                    rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                            </svg>
                        </div>
                        {{-- Konten --}}
                        <div class="flex-1 min-w-0 prose prose-sm dark:prose-invert max-w-none
                                    [&_ol]:list-decimal [&_ul]:list-disc
                                    [&_ol]:pl-5 [&_ul]:pl-5
                                    [&_li]:text-gray-700 dark:[&_li]:text-gray-300 [&_li]:leading-relaxed [&_li]:mb-1.5
                                    [&_p]:text-gray-700 dark:[&_p]:text-gray-300">
                            {!! \App\Support\SafeHtml::clean($tujuan) !!}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tab: Identitas --}}
            @if($identityNomenklatur || $identityNpsn)
            @php
                $logoIdentitas = \App\Models\Setting::get('logo');
            @endphp
            <div x-show="tab==='identitas'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex flex-col md:flex-row gap-8 items-start">

                    {{-- Logo besar di kiri --}}
                    @if($logoIdentitas)
                    <div class="flex-shrink-0 mx-auto md:mx-0">
                        <div class="w-44 h-44 rounded-2xl bg-white dark:bg-gray-800
                                    border border-gray-100 dark:border-gray-700
                                    shadow-lg flex items-center justify-center p-4">
                            <img src="{{ asset('storage/' . $logoIdentitas) }}"
                                 alt="Logo {{ $identityNomenklatur ?? 'Madrasah' }}"
                                 class="w-full h-full object-contain">
                        </div>
                        @if($identityNomenklatur)
                        <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-2 max-w-[11rem] leading-snug">
                            {{ $identityNomenklatur }}
                        </p>
                        @endif
                    </div>
                    @endif

                    {{-- Data identitas di kanan --}}
                    <div class="flex-1 min-w-0">
                        @php
                        $identityRows = array_filter([
                            'Nomenklatur'       => $identityNomenklatur,
                            'NPSN'              => $identityNpsn,
                            'NSM'               => $identityNsm,
                            'Akreditasi'        => $identityAkreditasi,
                            'Alamat'            => $identityAddress,
                            'Website'           => $identityWebsite,
                            'Adiwiyata'         => $identityAdiwiyata,
                            'Zona Integritas'   => $identityZI,
                        ]);
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach($identityRows as $label => $value)
                            <div class="flex flex-col p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                                <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider">{{ $label }}</span>
                                <span class="text-sm text-gray-800 dark:text-gray-200 font-semibold mt-0.5 leading-snug">{{ $value }}</span>
                            </div>
                            @endforeach
                        </div>

                        @if($identityNpsnUrl || $identityNsmUrl)
                        <div class="flex flex-wrap gap-3 mt-4">
                            @if($identityNpsnUrl)
                            <a href="{{ $identityNpsnUrl }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-school-green text-white rounded-xl text-sm font-semibold hover:bg-school-green-dark transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Profil NPSN
                            </a>
                            @endif
                            @if($identityNsmUrl)
                            <a href="{{ $identityNsmUrl }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Profil NSM
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
        @endif

    </div>
</section>
@endif

{{-- News Section --}}
<section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="section-divider">
                <h2 class="section-title">Berita Sekolah</h2>
            </div>
            <a href="{{ route('berita.index') }}" class="text-school-green hover:text-school-green-dark font-semibold text-sm flex items-center gap-1">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Tab Navigation --}}
        <div x-data="{ activeTab: 'terbaru' }" class="mb-6">
            <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                <button @click="activeTab = 'terbaru'" :class="activeTab === 'terbaru' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Terbaru</button>
                <button @click="activeTab = 'populer'" :class="activeTab === 'populer' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Populer</button>
                <button @click="activeTab = 'prestasi'" :class="activeTab === 'prestasi' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Prestasi</button>
                <button @click="activeTab = 'pengumuman'" :class="activeTab === 'pengumuman' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Pengumuman</button>
            </div>

            {{-- Terbaru --}}
            <div x-show="activeTab === 'terbaru'" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($latestNews as $news)
                    <article class="news-card" data-aos="fade-up">
                        <a href="{{ route('berita.show', $news->slug) }}" class="block overflow-hidden">
                            <img src="{{ $news->thumbnail_url }}" alt="{{ $news->title }}" class="news-card-image" loading="lazy">
                        </a>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="category-badge category-badge-green">{{ $news->category->name ?? 'Umum' }}</span>
                                <span class="text-xs text-gray-400">{{ $news->published_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-school-green transition-colors">
                                <a href="{{ route('berita.show', $news->slug) }}">{{ $news->title }}</a>
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 mb-4">{{ $news->excerpt }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $news->author->name ?? 'Admin' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ number_format($news->view_count) }}
                                </span>
                            </div>
                        </div>
                    </article>
                    @empty
                    <div class="col-span-3 text-center py-12 text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        <p>Belum ada berita</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Populer --}}
            <div x-show="activeTab === 'populer'" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($popularNews as $news)
                    <article class="news-card" data-aos="fade-up">
                        <a href="{{ route('berita.show', $news->slug) }}" class="block overflow-hidden">
                            <img src="{{ $news->thumbnail_url }}" alt="{{ $news->title }}" class="news-card-image" loading="lazy">
                        </a>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="category-badge category-badge-orange">{{ $news->category->name ?? 'Umum' }}</span>
                                <span class="text-xs text-gray-400">{{ $news->published_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-school-green transition-colors">
                                <a href="{{ route('berita.show', $news->slug) }}">{{ $news->title }}</a>
                            </h3>
                            <div class="flex items-center gap-1 text-xs text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                {{ number_format($news->view_count) }} pembaca
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>

            {{-- Prestasi --}}
            <div x-show="activeTab === 'prestasi'" class="mt-6">
                @if($prestasiNews->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($prestasiNews as $item)
                    <a href="{{ route('berita.show', $item->slug) }}"
                       class="group flex flex-col rounded-2xl border border-gray-100 dark:border-gray-700
                              overflow-hidden bg-white dark:bg-gray-800
                              hover:border-school-green hover:shadow-md transition-all duration-200"
                       data-aos="fade-up">

                        {{-- Gambar utama --}}
                        <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                            @if($item->thumbnail)
                                <img src="{{ $item->thumbnail_url }}"
                                     alt="{{ $item->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Konten --}}
                        <div class="flex flex-col flex-1 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                             bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    Prestasi
                                </span>
                                <span class="text-xs text-gray-400">{{ $item->published_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm leading-snug
                                       group-hover:text-school-green transition-colors line-clamp-2 flex-1">
                                {{ $item->title }}
                            </h3>
                            @if($item->excerpt)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 line-clamp-2">
                                {{ $item->excerpt }}
                            </p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <p class="text-sm">Belum ada berita prestasi. Tambahkan tag <strong>Prestasi</strong> pada berita terkait.</p>
                </div>
                @endif
            </div>

            {{-- Pengumuman --}}
            <div x-show="activeTab === 'pengumuman'" class="mt-6" x-cloak>
                @if($pengumumanNews->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($pengumumanNews as $item)
                    <a href="{{ route('berita.show', $item->slug) }}"
                       class="group flex flex-col rounded-2xl border border-gray-100 dark:border-gray-700
                              overflow-hidden bg-white dark:bg-gray-800
                              hover:border-school-green hover:shadow-md transition-all duration-200"
                       data-aos="fade-up">

                        {{-- Gambar utama --}}
                        <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                            @if($item->thumbnail)
                                <img src="{{ $item->thumbnail_url }}"
                                     alt="{{ $item->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Konten --}}
                        <div class="flex flex-col flex-1 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                             bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                    Pengumuman
                                </span>
                                <span class="text-xs text-gray-400">{{ $item->published_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm leading-snug
                                       group-hover:text-school-green transition-colors line-clamp-2 flex-1">
                                {{ $item->title }}
                            </h3>
                            @if($item->excerpt)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 line-clamp-2">
                                {{ $item->excerpt }}
                            </p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <p class="text-sm">Belum ada berita dengan kategori <strong>Pengumuman</strong>.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Konten Sosial Media --}}
@if($socialMediaContents->count() > 0)
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="section-divider">
                <h2 class="section-title">Konten Sosial Media</h2>
            </div>
        </div>

        <div x-data="{ activeSocialTab: 'youtube' }" class="mb-6">
            <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                <button @click="activeSocialTab = 'youtube'" :class="activeSocialTab === 'youtube' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">YouTube</button>
                <button @click="activeSocialTab = 'instagram'" :class="activeSocialTab === 'instagram' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Instagram</button>
                <button @click="activeSocialTab = 'tiktok'" :class="activeSocialTab === 'tiktok' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">TikTok</button>
            </div>

            @foreach(['youtube', 'instagram', 'tiktok'] as $platform)
            <div x-show="activeSocialTab === '{{ $platform }}'" class="mt-6">
                @php $platformItems = $socialMediaContents->where('platform', $platform); @endphp
                <div class="grid gap-6 justify-items-center {{ $platform === 'youtube' ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4' }}">
                    @forelse($platformItems as $content)
                    <article class="bg-gray-50 dark:bg-gray-800 rounded-2xl overflow-hidden shadow-card hover:-translate-y-1 transition-all duration-300 h-full {{ $content->platform === 'youtube' ? 'w-full max-w-sm' : '' }} {{ $content->platform === 'instagram' ? 'w-56' : '' }} {{ $content->platform === 'tiktok' ? 'w-44' : '' }}" data-aos="fade-up">
                        <a href="{{ $content->post_url }}" target="_blank" rel="noopener" class="block p-3 pb-2">
                            <div class="overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700 {{ $content->platform === 'youtube' ? 'aspect-video' : '' }} {{ $content->platform === 'instagram' ? 'aspect-[4/5]' : '' }} {{ $content->platform === 'tiktok' ? 'aspect-[9/16]' : '' }}">
                                <img src="{{ $content->thumbnail_url }}" alt="{{ $content->title }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                        </a>
                        <div class="{{ in_array($content->platform, ['instagram', 'tiktok']) ? 'px-3 pb-4 pt-1' : 'p-5 pt-2' }}">
                            <div class="mb-2">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $content->platform === 'youtube' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $content->platform === 'instagram' ? 'bg-pink-100 text-pink-700' : '' }}
                                    {{ $content->platform === 'tiktok' ? 'bg-gray-200 text-gray-700' : '' }}">
                                    {{ ucfirst($content->platform) }}
                                </span>
                            </div>
                            <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-2 mb-3 leading-snug {{ in_array($content->platform, ['instagram', 'tiktok']) ? 'text-sm min-h-[2.75rem]' : 'text-base min-h-[3rem]' }}">{{ $content->title }}</h3>
                            <a href="{{ $content->post_url }}" target="_blank" rel="noopener" class="text-school-green hover:text-school-green-dark font-semibold inline-flex items-center gap-1 {{ in_array($content->platform, ['instagram', 'tiktok']) ? 'text-xs' : 'text-sm' }}">
                                Lihat Konten
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </article>
                    @empty
                    <div class="col-span-full text-center py-10 text-gray-400">Belum ada konten {{ ucfirst($platform) }}.</div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Program Unggulan Madrasah --}}
@if($unggulanPrograms->count() > 0)
<section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="section-divider">
                <h2 class="section-title">Program Unggulan Madrasah</h2>
            </div>
            <a href="{{ route('program.unggulan') }}" class="text-school-green hover:text-school-green-dark font-semibold text-sm flex items-center gap-1">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($unggulanPrograms as $program)
            <article class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-card hover:-translate-y-1 transition-all duration-300" data-aos="fade-up">
                <img src="{{ $program->cover_url }}" alt="{{ $program->name }}" class="w-full h-48 object-cover" loading="lazy">
                <div class="p-5">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2">{{ $program->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">{{ $program->description }}</p>
                    @if(!empty($program->link))
                    <a href="{{ $program->link }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-semibold text-school-green hover:text-school-green-dark">
                        Lihat Detail
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Prestasi Section --}}
<section id="prestasi-section" class="py-12 bg-gradient-to-br from-school-green-dark to-school-green text-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8" data-aos="fade-up">
            <h2 class="text-3xl font-bold font-heading mb-2">Ringkasan Prestasi</h2>
            <p class="text-green-100">Periode Tahun Pelajaran {{ \App\Models\Setting::get('academic_year', '2024/2025') }}</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-10" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['total'] ?? 0 }}">0</div>
                <div class="text-green-100 text-sm">Total Prestasi</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['regional'] ?? 0 }}">0</div>
                <div class="text-green-100 text-sm">Tingkat Regional</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['provinsi'] ?? 0 }}">0</div>
                <div class="text-green-100 text-sm">Tingkat Provinsi</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['nasional'] ?? 0 }}">0</div>
                <div class="text-green-100 text-sm">Tingkat Nasional</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['internasional'] ?? 0 }}">0</div>
                <div class="text-green-100 text-sm">Tingkat Internasional</div>
            </div>
        </div>

        {{-- Prestasi Table --}}
        <div class="bg-white/10 backdrop-blur-sm rounded-2xl overflow-hidden" data-aos="fade-up" data-aos-delay="200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:700px">
                    <colgroup>
                        <col style="width:3rem">          {{-- No --}}
                        <col style="width:16%">           {{-- Nama Siswa --}}
                        <col style="width:24%">           {{-- Nama Lomba --}}
                        <col style="width:14%">           {{-- Tingkat --}}
                        <col style="width:10%">           {{-- Juara --}}
                        <col style="width:22%">           {{-- Penyelenggara --}}
                        <col style="width:11%">           {{-- Tanggal --}}
                    </colgroup>
                    <thead>
                        <tr class="bg-white/10">
                            <th class="px-4 py-3 text-left font-semibold">No</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama Siswa</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama Lomba</th>
                            <th class="px-4 py-3 text-left font-semibold">Tingkat</th>
                            <th class="px-4 py-3 text-left font-semibold">Juara</th>
                            <th class="px-4 py-3 text-left font-semibold">Penyelenggara</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prestasiList as $index => $prestasi)
                        <tr class="border-t border-white/10 hover:bg-white/5 transition-colors align-top">
                            <td class="px-4 py-3 text-green-100">{{ $prestasiList->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium leading-snug">{{ $prestasi->student_name }}</td>
                            <td class="px-4 py-3 text-green-100 leading-snug">{{ $prestasi->competition_name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 bg-white/20 rounded-full text-xs leading-snug">
                                    @php
                                        $levelLabel = match($prestasi->level) {
                                            'regional'      => 'Regional / Kota',
                                            'provinsi'      => 'Provinsi',
                                            'nasional'      => 'Nasional',
                                            'internasional' => 'Internasional',
                                            default         => ucfirst($prestasi->level),
                                        };
                                    @endphp
                                    {{ $levelLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block px-2 py-0.5 bg-orange-700 text-white rounded-full text-xs font-semibold leading-snug">{{ $prestasi->rank }}</span>
                            </td>
                            <td class="px-4 py-3 text-green-100 leading-snug">{{ $prestasi->organizer ?: '-' }}</td>
                            <td class="px-4 py-3 text-green-100 whitespace-nowrap">
                                {{ $prestasi->achievement_date ? $prestasi->achievement_date->translatedFormat('d M Y') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-green-100">Belum ada data prestasi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($prestasiList->hasPages())
            <div class="px-4 py-3 border-t border-white/10 flex items-center justify-between gap-4 flex-wrap">
                <p class="text-xs text-green-100">
                    Menampilkan {{ $prestasiList->firstItem() }}–{{ $prestasiList->lastItem() }} dari {{ $prestasiList->total() }} prestasi
                </p>
                <div class="prestasi-pagination flex items-center gap-1 text-sm">
                    {{-- Prev --}}
                    @if($prestasiList->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white/30 cursor-not-allowed select-none">&lsaquo;</span>
                    @else
                        <a href="{{ $prestasiList->previousPageUrl() }}#prestasi-section"
                           class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-colors">&lsaquo;</a>
                    @endif

                    {{-- Page numbers --}}
                    @foreach(range(1, $prestasiList->lastPage()) as $page)
                        @if($page == $prestasiList->currentPage())
                            <span class="px-3 py-1.5 rounded-lg bg-white text-school-green font-bold">{{ $page }}</span>
                        @else
                            <a href="{{ $prestasiList->url($page) }}#prestasi-section"
                               class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($prestasiList->hasMorePages())
                        <a href="{{ $prestasiList->nextPageUrl() }}#prestasi-section"
                           class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white transition-colors">&rsaquo;</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white/30 cursor-not-allowed select-none">&rsaquo;</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Sarpras Carousel --}}
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="section-divider">
                <h2 class="section-title">Sarana & Prasarana</h2>
            </div>
        </div>
        <div class="sarpras-swiper swiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                @foreach($sarpras as $item)
                <div class="swiper-slide">
                    <div class="rounded-2xl overflow-hidden shadow-card group">
                        <div class="relative overflow-hidden h-48">
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-4">
                                <h3 class="text-white font-semibold">{{ $item->name }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="sarpras-pagination swiper-pagination mt-4"></div>
        </div>
    </div>
</section>

{{-- Testimoni Alumni --}}
@if(($showTestimonialsSection ?? true) && $testimonials->count() > 0)
<section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8" data-aos="fade-up">
            <h2 class="section-title">Testimoni Alumni</h2>
            <p class="section-subtitle">Apa kata mereka tentang MTsN 2 Kota Malang</p>
        </div>
        <div class="testimoni-swiper swiper" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper-wrapper">
                @foreach($testimonials as $testimonial)
                <div class="swiper-slide">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-card h-full">
                        <div class="flex items-center gap-1 mb-4">
                            @for($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4 italic">"{{ $testimonial->content }}"</p>
                        <div class="flex items-center gap-3">
                            @if($testimonial->photo)
                            <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                            <div class="w-10 h-10 rounded-full bg-school-green flex items-center justify-center text-white font-bold text-sm">{{ substr($testimonial->name, 0, 1) }}</div>
                            @endif
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white text-sm">{{ $testimonial->name }}</div>
                                <div class="text-xs text-gray-400">Alumni {{ $testimonial->graduation_year }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="testimoni-pagination swiper-pagination mt-6"></div>
        </div>
    </div>
</section>
@endif

{{-- Portal Aplikasi --}}
@if($portalApps->count() > 0)
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8" data-aos="fade-up">
            <h2 class="section-title">Portal Aplikasi</h2>
            <p class="section-subtitle">Akses berbagai layanan digital MTsN 2 Kota Malang</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($portalApps as $app)
            <a href="{{ $app->link }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl hover:bg-green-50 dark:hover:bg-green-900/20 hover:shadow-card transition-all duration-300 hover:-translate-y-1 group" data-aos="fade-up">
                <img src="{{ $app->logo_url }}" alt="{{ $app->name }}" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">{{ $app->name }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
