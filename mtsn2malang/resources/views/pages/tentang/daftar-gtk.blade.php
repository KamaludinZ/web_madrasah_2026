@extends('layouts.app')
@section('title', 'Daftar GTK - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Tentang Kami</li><li>/</li><li class="text-white font-medium">Daftar GTK</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Daftar Guru & Tenaga Kependidikan</h1>
        <p class="text-green-100 mt-2">Tenaga pendidik dan kependidikan MTsN 2 Kota Malang</p>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    {{-- Guru --}}
    <div class="mb-12">
        <div class="section-divider mb-6"><h2 class="section-title">Daftar Guru</h2></div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @forelse($guru as $g)
            <div class="gtk-card select-none" data-aos="fade-up" oncontextmenu="return false;">
                <div class="relative overflow-hidden rounded-xl">
                    <img
                        src="{{ $g->photo_url }}"
                        alt="{{ $g->name }}"
                        class="gtk-photo gtk-photo-protected"
                        loading="lazy"
                        draggable="false"
                    >
                    <div class="gtk-watermark" aria-hidden="true">MTsN 2 Kota Malang</div>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $g->name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $g->position }}</p>
                @if($g->subject)<p class="text-xs text-school-green mt-0.5">{{ $g->subject }}</p>@endif
            </div>
            @empty
            <div class="col-span-5 text-center py-12 text-gray-400">Belum ada data guru</div>
            @endforelse
        </div>
    </div>

    {{-- Tendik --}}
    <div>
        <div class="section-divider mb-6"><h2 class="section-title">Tenaga Kependidikan</h2></div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @forelse($tendik as $t)
            <div class="gtk-card select-none" data-aos="fade-up" oncontextmenu="return false;">
                <div class="relative overflow-hidden rounded-xl">
                    <img
                        src="{{ $t->photo_url }}"
                        alt="{{ $t->name }}"
                        class="gtk-photo gtk-photo-protected"
                        loading="lazy"
                        draggable="false"
                    >
                    <div class="gtk-watermark" aria-hidden="true">MTsN 2 Kota Malang</div>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $t->name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $t->position }}</p>
            </div>
            @empty
            <div class="col-span-5 text-center py-12 text-gray-400">Belum ada data tenaga kependidikan</div>
            @endforelse
        </div>
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
