@extends('layouts.app')
@section('title', 'Pencarian' . ($query ? ': ' . $query : '') . ' - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold font-heading mb-4">Pencarian</h1>
        <form action="{{ route('search.index') }}" method="GET" class="flex gap-3 max-w-2xl">
            <input type="text" name="q" value="{{ $query }}" placeholder="Cari berita, kategori, tag..." class="flex-1 px-4 py-3 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-white/50">
            <button type="submit" class="px-6 py-3 bg-school-orange text-white font-semibold rounded-xl hover:bg-school-orange-dark transition-colors">Cari</button>
        </form>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    {{-- Filter --}}
    <div class="flex flex-wrap gap-3 mb-6">
        @foreach($categories as $cat)
        <a href="{{ route('search.index', ['q' => $query, 'kategori' => $cat->slug]) }}" class="px-3 py-1.5 rounded-full text-sm {{ $kategori === $cat->slug ? 'bg-school-green text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200' }} transition-colors">{{ $cat->name }}</a>
        @endforeach
    </div>

    @if($query || $kategori || $tag)
    <div class="mb-4 text-gray-600 dark:text-gray-400 text-sm">
        @if($results->total() > 0)
        Ditemukan <strong>{{ $results->total() }}</strong> hasil untuk "<strong>{{ $query }}</strong>"
        @else
        Tidak ada hasil untuk "<strong>{{ $query }}</strong>"
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($results as $item)
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
        <div class="col-span-3 text-center py-16 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-lg font-medium">Tidak ada hasil ditemukan</p>
            <p class="text-sm mt-2">Coba kata kunci yang berbeda atau periksa ejaan Anda</p>
        </div>
        @endforelse
    </div>

    @if($results->hasPages())
    <div class="mt-8">{{ $results->links() }}</div>
    @endif
    @else
    <div class="text-center py-16 text-gray-400">
        <svg class="w-20 h-20 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <p class="text-xl font-medium">Masukkan kata kunci untuk mencari</p>
        <p class="text-sm mt-2">Anda dapat mencari berdasarkan judul, kategori, atau tag</p>
    </div>
    @endif
</div>
@endsection
