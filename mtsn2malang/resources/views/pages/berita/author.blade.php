@extends('layouts.app')
@section('title', 'Berita oleh ' . $author->name . ' - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <div class="flex items-center gap-6">
            <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}" class="w-20 h-20 rounded-full border-4 border-white/30">
            <div>
                <h1 class="text-3xl font-bold font-heading">{{ $author->name }}</h1>
                <p class="text-green-200 mt-1">{{ $news->total() }} berita ditulis</p>
                @if($author->bio)<p class="text-green-100 text-sm mt-2">{{ $author->bio }}</p>@endif
            </div>
        </div>
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
                    <span class="category-badge category-badge-green">{{ $item->category->name ?? 'Umum' }}</span>
                    <span class="text-xs text-gray-400">{{ $item->published_at?->format('d M Y') }}</span>
                </div>
                <h2 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-school-green transition-colors">
                    <a href="{{ route('berita.show', $item->slug) }}">{{ $item->title }}</a>
                </h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2">{{ $item->excerpt }}</p>
            </div>
        </article>
        @empty
        <div class="col-span-3 text-center py-16 text-gray-400"><p>Belum ada berita</p></div>
        @endforelse
    </div>
    @if($news->hasPages())<div class="mt-8">{{ $news->links() }}</div>@endif
</div>
@endsection
