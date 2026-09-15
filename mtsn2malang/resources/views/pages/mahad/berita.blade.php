@extends('layouts.app')

@section('title', "Berita Ma'had - MTsN 2 Kota Malang")
@section('description', "Kumpulan berita Ma'had terbaru dari MTsN 2 Kota Malang")

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li><span>/</span></li>
                <li><a href="{{ route('mahad.home') }}" class="hover:text-white">Ma'had</a></li>
                <li><span>/</span></li>
                <li class="text-white font-medium">Berita Ma'had</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Berita Ma'had</h1>
        <p class="text-green-100 mt-2">Informasi terkini kegiatan Ma'had MTsN 2 Kota Malang</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($news as $item)
            <article class="news-card" data-aos="fade-up">
                <a href="{{ route('berita.show', $item->slug) }}" class="block overflow-hidden">
                    <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="news-card-image" loading="lazy">
                </a>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="category-badge category-badge-green">{{ $item->category->name ?? 'Ma\'had' }}</span>
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
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-16 text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <p class="text-lg font-medium">Belum ada berita Ma'had</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $news->links() }}
    </div>
</div>
@endsection
