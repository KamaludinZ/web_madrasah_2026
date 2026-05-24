@extends('layouts.app')
@section('title', 'Daftar GTK - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Tentang Kami</li><li>/</li><li class="text-white font-medium">Daftar GTK</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Daftar Guru & Tenaga Kependidikan</h1>
        <p class="text-green-200 mt-2">Tenaga pendidik dan kependidikan MTsN 2 Kota Malang</p>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    {{-- Guru --}}
    <div class="mb-12">
        <div class="section-divider mb-6"><h2 class="section-title">Daftar Guru</h2></div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @forelse($guru as $g)
            <div class="gtk-card" data-aos="fade-up">
                <img src="{{ $g->photo_url }}" alt="{{ $g->name }}" class="gtk-photo" loading="lazy">
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
            <div class="gtk-card" data-aos="fade-up">
                <img src="{{ $t->photo_url }}" alt="{{ $t->name }}" class="gtk-photo" loading="lazy">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $t->name }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $t->position }}</p>
            </div>
            @empty
            <div class="col-span-5 text-center py-12 text-gray-400">Belum ada data tenaga kependidikan</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
