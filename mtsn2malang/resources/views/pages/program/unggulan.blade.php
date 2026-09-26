@extends('layouts.app')

@section('title', 'Program Unggulan - MTsN 2 Kota Malang')
@section('description', 'Daftar program unggulan MTsN 2 Kota Malang.')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li class="text-white font-medium">Program Unggulan</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Program Unggulan</h1>
        <p class="text-green-100 mt-2">Program terbaik MTsN 2 Kota Malang untuk penguatan kompetensi dan karakter siswa.</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    @if(isset($content) && $content)
    <div class="max-w-4xl mx-auto mb-10">
        @if($content->image)
        <div class="mb-8 rounded-2xl overflow-hidden shadow-card">
            <img src="{{ asset('storage/' . $content->image) }}"
                 alt="{{ $content->title ?? 'Program Unggulan' }}"
                 class="w-full h-auto object-contain">
        </div>
        @endif
        @if($content->title)
        <h2 class="text-2xl font-bold font-heading text-school-green mb-6">{{ $content->title }}</h2>
        @endif
        @if($content->content)
        <div class="prose prose-lg dark:prose-invert mx-auto prose-headings:text-school-green prose-headings:font-heading">
            {!! \App\Support\SafeHtml::clean($content->content) !!}
        </div>
        @endif
    </div>
    @endif

    @if($programs->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($programs as $program)
        <article class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-card hover:-translate-y-1 transition-all duration-300" data-aos="fade-up">
            <img src="{{ $program->cover_url }}" alt="{{ $program->name }}" class="w-full h-48 object-cover" loading="lazy">
            <div class="p-5">
                <h2 class="font-bold text-gray-900 dark:text-white mb-2">{{ $program->name }}</h2>
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
    @else
    <div class="text-center py-16 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
        </svg>
        <p>Belum ada program unggulan yang tersedia.</p>
    </div>
    @endif

    @include('partials.related-news', ['title' => 'Berita Program Unggulan'])
</div>
@endsection
