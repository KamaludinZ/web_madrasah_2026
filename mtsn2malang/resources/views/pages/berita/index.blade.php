@extends('layouts.app')

@section('title', 'Berita - MTsN 2 Kota Malang')
@section('description', 'Kumpulan berita terbaru dari MTsN 2 Kota Malang')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li><span>/</span></li>
                <li class="text-white font-medium">Berita</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Berita Sekolah</h1>
        <p class="text-green-200 mt-2">Informasi terkini dari MTsN 2 Kota Malang</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Main Content --}}
        <div class="flex-1">
            {{-- Filter Bar --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <a href="{{ route('berita.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ !request('kategori') ? 'bg-school-green text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }} transition-colors">Semua</a>
                @foreach($categories as $cat)
                <a href="{{ route('berita.index', ['kategori' => $cat->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ request('kategori') === $cat->slug ? 'bg-school-green text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }} transition-colors">{{ $cat->name }}</a>
                @endforeach
            </div>

            {{-- News Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($news as $item)
                <article class="news-card" data-aos="fade-up">
                    <a href="{{ route('berita.show', $item->slug) }}" class="block overflow-hidden">
                        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="news-card-image" loading="lazy">
                    </a>
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="category-badge category-badge-green">{{ $item->category->name ?? 'Umum' }}</span>
                            <span class="text-xs text-gray-400">{{ $item->published_at?->format('d M Y') }}</span>
                        </div>
                        <h2 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-school-green transition-colors">
                            <a href="{{ route('berita.show', $item->slug) }}">{{ $item->title }}</a>
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-3 mb-4">{{ $item->excerpt }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $item->author->name ?? 'Admin' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                {{ number_format($item->view_count) }}
                            </span>
                        </div>
                    </div>
                </article>
                @empty
                <div class="col-span-2 text-center py-16 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <p class="text-lg font-medium">Belum ada berita</p>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $news->links() }}
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="lg:w-80 flex-shrink-0">
            {{-- Search --}}
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Cari Berita</h3>
                <form action="{{ route('search.index') }}" method="GET">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Cari..." class="w-full pl-4 pr-10 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-green-200 focus:border-school-green dark:bg-gray-700 dark:text-white outline-none transition-all">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-school-green">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Popular News --}}
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Berita Populer</h3>
                <div class="space-y-4">
                    @foreach($popularNews as $popular)
                    <a href="{{ route('berita.show', $popular->slug) }}" class="flex gap-3 group">
                        <img src="{{ $popular->thumbnail_url }}" alt="{{ $popular->title }}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-school-green transition-colors line-clamp-2">{{ $popular->title }}</h4>
                            <span class="text-xs text-gray-400">{{ number_format($popular->view_count) }} pembaca</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Categories --}}
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Kategori</h3>
                <div class="space-y-2">
                    @foreach($categories as $cat)
                    <a href="{{ route('berita.index', array_merge(request()->except(['page', 'kategori']), ['kategori' => $cat->slug])) }}" class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 hover:text-school-green transition-colors">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 px-2 py-0.5 rounded-full">{{ $cat->news_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Arsip Berita --}}
            <div class="sidebar-widget">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="sidebar-title mb-0">Arsip Berita</h3>
                    @if($selectedYear || $selectedMonth)
                        <a href="{{ route('berita.index', request()->except(['tahun', 'bulan', 'page'])) }}" class="text-xs text-school-green hover:underline">Reset</a>
                    @endif
                </div>

                <div class="space-y-2">
                    @forelse($archives as $yearData)
                        <details class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2" {{ $selectedYear === $yearData['year'] ? 'open' : '' }}>
                            <summary class="cursor-pointer list-none flex items-center justify-between">
                                <span class="font-semibold text-sm text-gray-800 dark:text-gray-100">{{ $yearData['year'] }}</span>
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200 px-2 py-0.5 rounded-full">{{ $yearData['total'] }}</span>
                            </summary>

                            <div class="mt-2 space-y-1">
                                @foreach($yearData['months'] as $monthData)
                                    @php
                                        $isActiveArchive = $selectedYear === $yearData['year'] && $selectedMonth === $monthData['month'];
                                        $archiveUrl = route('berita.index', array_merge(
                                            request()->except(['page']),
                                            ['tahun' => $yearData['year'], 'bulan' => str_pad((string) $monthData['month'], 2, '0', STR_PAD_LEFT)]
                                        ));
                                    @endphp
                                    <a href="{{ $archiveUrl }}" class="flex items-center justify-between rounded-md px-2 py-1.5 text-sm transition-colors {{ $isActiveArchive ? 'bg-school-green text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        <span>{{ $monthData['label'] }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $isActiveArchive ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-100' }}">{{ $monthData['total'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada arsip berita.</p>
                    @endforelse
                </div>
            </div>

            {{-- Infografis --}}
            @if($infografis->count() > 0)
            <div class="sidebar-widget">
                <h3 class="sidebar-title">Infografis</h3>
                <div class="space-y-3">
                    @foreach($infografis as $info)
                    <a href="{{ $info->image_url }}" class="glightbox block rounded-xl overflow-hidden group">
                        <img src="{{ $info->image_url }}" alt="{{ $info->title }}" class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>
@endsection
