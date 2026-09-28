@extends('layouts.app')

@section('title', 'Struktur Komite - MTsN 2 Kota Malang')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Tentang Kami</li>
                <li>/</li>
                <li class="text-white font-medium">Struktur Komite</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Struktur Komite</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-7xl mx-auto">
        @if($content && $content->image)
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-card p-3 md:p-5">
                <div class="relative select-none" oncontextmenu="return false;">
                    <img
                        src="{{ asset('storage/' . $content->image) }}"
                        alt="Struktur Komite MTsN 2 Kota Malang"
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
                <h2 class="text-xl font-bold text-school-green mb-2">Struktur Komite Belum Tersedia</h2>
                <p class="text-gray-600 dark:text-gray-300">Silakan unggah gambar struktur melalui menu admin pada Konten Halaman.</p>
            </div>
        @endif
    </div>
</div>
@endsection
