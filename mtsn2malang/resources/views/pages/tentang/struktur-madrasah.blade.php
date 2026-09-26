@extends('layouts.app')

@section('title', 'Struktur Madrasah - MTsN 2 Kota Malang')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Tentang Kami</li>
                <li>/</li>
                <li class="text-white font-medium">Struktur Madrasah</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Struktur Madrasah</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-7xl mx-auto space-y-10">

        {{-- ===== UNSUR PIMPINAN ===== --}}
        @if($leaders->isNotEmpty())
        <div>
            <h2 class="text-2xl font-bold font-heading text-school-green mb-6 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Unsur Pimpinan
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">
                @foreach($leaders as $leader)
                <div class="gtk-card select-none" data-aos="fade-up" oncontextmenu="return false;">
                    {{-- Foto --}}
                    <div class="relative overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700">
                        @if($leader->photo)
                            <img
                                src="{{ asset('storage/' . $leader->photo) }}"
                                alt="{{ $leader->name }}"
                                class="gtk-photo gtk-photo-protected"
                                loading="lazy"
                                draggable="false"
                            >
                            <div class="gtk-watermark" aria-hidden="true">MTsN 2 Kota Malang</div>
                        @else
                            <div class="aspect-[2/3] flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mt-2">{{ $leader->name }}</h3>
                    <p class="text-xs text-school-green mt-0.5">{{ $leader->position }}</p>
                    @if($leader->nip)
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-mono mt-0.5">NIP. {{ $leader->nip }}</p>
                    @endif
                    @if($leader->period)
                    <span class="inline-block mt-1.5 px-2 py-0.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-xs rounded-full">
                        {{ $leader->period }}
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
        {{-- ===== END UNSUR PIMPINAN ===== --}}

        {{-- ===== BAGAN STRUKTUR ===== --}}
        <div>
            @if($leaders->isNotEmpty())
            <h2 class="text-2xl font-bold font-heading text-school-green mb-6 flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Bagan Struktur Organisasi
            </h2>
            @endif

            @if($content && $content->image)
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-card p-3 md:p-5">
                    <div class="relative select-none" oncontextmenu="return false;">
                        <img
                            src="{{ asset('storage/' . $content->image) }}"
                            alt="Struktur Madrasah MTsN 2 Kota Malang"
                            class="w-full h-auto object-contain rounded-xl pointer-events-none"
                            draggable="false"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 rounded-xl" aria-hidden="true"></div>
                    </div>
                </div>
            @elseif($content && $content->content)
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-card p-6 md:p-8">
                    <div class="prose prose-lg dark:prose-invert mx-auto prose-headings:text-school-green prose-headings:font-heading">
                        {!! \App\Support\SafeHtml::clean($content->content) !!}
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-card p-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h2 class="text-xl font-bold text-school-green mb-2">Bagan Struktur Belum Tersedia</h2>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Silakan unggah gambar struktur melalui menu admin pada Konten Halaman.</p>
                </div>
            @endif
        </div>
        {{-- ===== END BAGAN STRUKTUR ===== --}}

    </div>
</div>

<style>
    .gtk-photo-protected {
        -webkit-user-drag: none;
        user-select: none;
        pointer-events: none;
    }

    .gtk-watermark {
        position: absolute;
        inset: auto 0 0 0;
        padding: 0.25rem 0.5rem;
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-align: center;
        color: rgba(255, 255, 255, 0.85);
        background: linear-gradient(to top, rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0));
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.45);
        pointer-events: none;
    }
</style>
@endsection
