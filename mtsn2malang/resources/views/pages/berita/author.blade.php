@extends('layouts.app')
@section('title', $author->name . ' - Profil Penulis MTsN 2 Kota Malang')
@section('description', $author->bio ?? 'Berita yang ditulis oleh ' . $author->name . ' di MTsN 2 Kota Malang.')

@section('content')

{{-- ===== HERO PROFIL PENULIS ===== --}}
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-14 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-6">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Beranda</a></li>
                <li>/</li>
                <li><a href="{{ route('berita.index') }}" class="hover:text-white transition-colors">Berita</a></li>
                <li>/</li>
                <li class="text-white font-medium">Profil Penulis</li>
            </ol>
        </nav>

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                <img
                    src="{{ $author->avatar_url }}"
                    alt="{{ $author->name }}"
                    class="w-28 h-28 rounded-full object-cover ring-4 ring-white/30 shadow-xl"
                >
                <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-green-400 ring-2 ring-white flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            {{-- Info --}}
            <div class="text-center sm:text-left flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                    @php
                        $roleName = $author->getRoleNames()->first();
                        $roleDisplay = $roleName ? \App\Support\RoleDisplay::label($roleName) : null;
                    @endphp
                    @if($roleDisplay)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white">
                        {{ $roleDisplay }}
                    </span>
                    @endif
                </div>

                <h1 class="text-3xl md:text-4xl font-bold font-heading leading-tight">{{ $author->name }}</h1>

                <p class="text-green-100 text-sm mt-2 flex items-center justify-center sm:justify-start gap-1.5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    {{ $news->total() }} artikel diterbitkan
                </p>

                @if($author->bio)
                <p class="text-green-100 text-sm md:text-base mt-4 max-w-2xl leading-relaxed">
                    {{ $author->bio }}
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== KONTEN UTAMA ===== --}}
<div class="container mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ===== ARTIKEL PENULIS ===== --}}
        <main class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold font-heading text-gray-900 dark:text-white">
                    Artikel oleh {{ $author->name }}
                </h2>
                <span class="text-sm text-gray-400">{{ $news->total() }} artikel</span>
            </div>

            @forelse($news as $item)
            <article class="flex gap-4 md:gap-5 bg-white dark:bg-gray-800 rounded-2xl shadow-card overflow-hidden mb-5 group hover:shadow-md transition-shadow" data-aos="fade-up">
                {{-- Thumbnail --}}
                <a href="{{ route('berita.show', $item->slug) }}" class="flex-shrink-0 w-32 md:w-44 overflow-hidden">
                    <img
                        src="{{ $item->thumbnail_url }}"
                        alt="{{ $item->title }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                    >
                </a>

                {{-- Content --}}
                <div class="flex-1 min-w-0 py-4 pr-4 md:py-5 md:pr-5 flex flex-col justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="category-badge category-badge-green text-xs">
                                {{ $item->category->name ?? 'Umum' }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $item->published_at?->format('d M Y') }}
                            </span>
                        </div>

                        <h3 class="font-bold text-gray-900 dark:text-white text-sm md:text-base line-clamp-2 leading-snug mb-2 group-hover:text-school-green transition-colors">
                            <a href="{{ route('berita.show', $item->slug) }}">{{ $item->title }}</a>
                        </h3>

                        <p class="text-gray-500 dark:text-gray-400 text-xs md:text-sm line-clamp-2 leading-relaxed">
                            {{ $item->excerpt }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between mt-3">
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>{{ number_format($item->view_count) }}</span>
                        </div>
                        <a href="{{ route('berita.show', $item->slug) }}"
                           class="text-xs font-semibold text-school-green hover:underline flex items-center gap-1">
                            Baca
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
            @empty
            <div class="text-center py-20 text-gray-400">
                <div class="text-5xl mb-4">📝</div>
                <p class="text-lg font-medium text-gray-500 dark:text-gray-400">Belum ada artikel</p>
                <p class="text-sm mt-1">Penulis ini belum menerbitkan artikel.</p>
            </div>
            @endforelse

            @if($news->hasPages())
            <div class="mt-8">{{ $news->links() }}</div>
            @endif
        </main>

        {{-- ===== SIDEBAR ===== --}}
        <aside class="lg:w-80 flex-shrink-0 space-y-6">

            {{-- Kartu Profil Ringkas --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card overflow-hidden">
                <div class="h-16 bg-gradient-to-r from-school-green-dark to-school-green"></div>
                <div class="px-5 pb-5 -mt-8">
                    <img
                        src="{{ $author->avatar_url }}"
                        alt="{{ $author->name }}"
                        class="w-16 h-16 rounded-full object-cover ring-4 ring-white dark:ring-gray-800 shadow-md mb-3"
                    >
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">{{ $author->name }}</h3>
                    @if($roleDisplay)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-school-green mt-1">
                        {{ $roleDisplay }}
                    </span>
                    @endif
                    @if($author->bio)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed">
                        {{ $author->bio }}
                    </p>
                    @endif
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 text-school-green flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        <span><strong class="text-gray-900 dark:text-white">{{ $news->total() }}</strong> artikel diterbitkan</span>
                    </div>
                </div>
            </div>

            {{-- Artikel Populer --}}
            @if($popularNews->count() > 0)
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Artikel Populer</h3>
                <div class="space-y-4">
                    @foreach($popularNews as $popular)
                    <a href="{{ route('berita.show', $popular->slug) }}" class="flex gap-3 group">
                        <img
                            src="{{ $popular->thumbnail_url }}"
                            alt="{{ $popular->title }}"
                            class="w-16 h-16 rounded-xl object-cover flex-shrink-0"
                            loading="lazy"
                        >
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-school-green transition-colors line-clamp-2 leading-snug">
                                {{ $popular->title }}
                            </h4>
                            <div class="flex items-center gap-1 mt-1 text-xs text-gray-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ number_format($popular->view_count) }} dibaca
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Kategori --}}
            @if($categories->count() > 0)
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Kategori</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($categories as $cat)
                    <a href="{{ route('berita.index', ['kategori' => $cat->slug]) }}"
                       class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm rounded-full hover:bg-school-green hover:text-white transition-colors">
                        {{ $cat->name }}
                        @if($cat->news_count > 0)
                        <span class="text-xs opacity-60">({{ $cat->news_count }})</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </aside>
    </div>
</div>

@endsection
