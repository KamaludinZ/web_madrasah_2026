@extends('layouts.app')

@section('title', 'Ikatan Alumni - MTsN 2 Kota Malang')
@section('description', 'Informasi Ikatan Alumni MTsN 2 Kota Malang beserta profil koordinator setiap angkatan.')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Tentang Kami</li>
                <li>/</li>
                <li class="text-white font-medium">Ikatan Alumni</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Ikatan Alumni</h1>
    </div>
</div>

<div class="container mx-auto px-4 py-10 space-y-10">
    <div class="max-w-4xl mx-auto">
        @if($content?->content)
            <div class="prose prose-lg dark:prose-invert max-w-none prose-headings:text-school-green prose-headings:font-heading">
                {!! $content->content !!}
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
                <p class="text-gray-700 dark:text-gray-300">
                    Halaman Ikatan Alumni menampilkan profil koordinator alumni pada tiap angkatan.
                </p>
            </div>
        @endif
    </div>

    <section>
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-2xl font-bold font-heading text-school-green dark:text-green-400">Koordinator Alumni per Angkatan</h2>
        </div>

        @if($coordinators->count())
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($coordinators as $coordinator)
                    <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-card overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                @if($coordinator->foto_url)
                                    <img src="{{ $coordinator->foto_url }}" alt="{{ $coordinator->nama_koordinator }}" class="w-16 h-16 rounded-full object-cover">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-school-green/10 text-school-green font-bold flex items-center justify-center text-lg">
                                        {{ strtoupper(substr($coordinator->nama_koordinator, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Angkatan</p>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $coordinator->angkatan }}</h3>
                                </div>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Koordinator</p>
                            <p class="font-semibold text-gray-900 dark:text-white mb-3">{{ $coordinator->nama_koordinator }}</p>

                            @if($coordinator->kontak)
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                    <span class="font-medium">Kontak:</span> {{ $coordinator->kontak }}
                                </p>
                            @endif

                            @if($coordinator->deskripsi)
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $coordinator->deskripsi }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 text-center text-gray-600 dark:text-gray-300">
                Data koordinator alumni belum tersedia.
            </div>
        @endif
    </section>

    @if($alumniNews->count())
        <section>
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl font-bold font-heading text-school-green dark:text-green-400">Berita Terkait Alumni</h2>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($alumniNews as $item)
                    <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-card overflow-hidden">
                        @if($item->featured_image_url)
                            <a href="{{ url('/berita/' . $item->slug) }}" class="block">
                                <img src="{{ $item->featured_image_url }}" alt="{{ $item->title }}" class="w-full h-44 object-cover">
                            </a>
                        @endif
                        <div class="p-5">
                            <h3 class="font-bold text-gray-900 dark:text-white leading-snug mb-2">
                                <a href="{{ url('/berita/' . $item->slug) }}" class="hover:text-school-green">
                                    {{ $item->title }}
                                </a>
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ optional($item->published_at)->format('d M Y') }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $alumniNews->links() }}
            </div>
        </section>
    @endif
</div>
@endsection
