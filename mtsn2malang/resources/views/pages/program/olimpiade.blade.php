@extends('layouts.app')
@section('title', 'Kelas Olimpiade - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Program</li><li>/</li><li class="text-white font-medium">Kelas Olimpiade</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Kelas Olimpiade</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">
        @if($content)
            @if($content->image)
            <div class="mb-8 rounded-2xl overflow-hidden shadow-card">
                <img src="{{ asset('storage/' . $content->image) }}"
                     alt="{{ $content->title ?? 'Kelas Olimpiade' }}"
                     class="w-full h-auto object-contain">
            </div>
            @endif
            @if($content->title)
            <h2 class="text-2xl font-bold font-heading text-school-green mb-6">{{ $content->title }}</h2>
            @endif
            @if($content->content)
            <div class="prose prose-lg dark:prose-invert max-w-none prose-headings:text-school-green prose-headings:font-heading">
                {!! $content->content !!}
            </div>
            @endif
        @else
        <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-card text-center">
            <p class="text-gray-500 dark:text-gray-400">Konten halaman Kelas Olimpiade belum diatur. Silakan isi melalui panel admin → Konten Halaman.</p>
        </div>
        @endif
    </div>
</div>
@endsection
