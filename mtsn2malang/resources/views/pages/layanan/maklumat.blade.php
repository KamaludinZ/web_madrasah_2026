@extends('layouts.app')
@section('title', 'Maklumat Pelayanan - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Layanan</li><li>/</li><li class="text-white font-medium">Maklumat Pelayanan</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Maklumat Pelayanan</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">
        @if($content)
            {{-- Gambar utama jika ada --}}
            @if($content->image)
            <div class="mb-8 rounded-2xl overflow-hidden shadow-card">
                <img src="{{ asset('storage/' . $content->image) }}"
                     alt="{{ $content->title ?? 'Maklumat Pelayanan' }}"
                     class="w-full h-auto object-contain">
            </div>
            @endif

            {{-- Judul jika ada --}}
            @if($content->title)
            <h2 class="text-2xl font-bold font-heading text-school-green mb-6">{{ $content->title }}</h2>
            @endif

            {{-- Konten teks jika ada --}}
            @if($content->content)
            <div class="prose prose-lg dark:prose-invert max-w-none prose-headings:text-school-green prose-headings:font-heading">
                {!! $content->content !!}
            </div>
            @endif
        @else
        <div class="space-y-8">
            <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-card" data-aos="fade-up">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Maklumat Pelayanan</h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    Informasi maklumat pelayanan belum diatur. Silakan perbarui konten melalui panel admin pada halaman konten maklumat.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
