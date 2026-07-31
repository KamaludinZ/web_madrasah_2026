@extends('layouts.app')
@section('title', 'Informasi Berkala - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>PPID</li><li>/</li><li class="text-white font-medium">Informasi Berkala</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Informasi Berkala</h1>
        <p class="text-green-200 mt-2">Informasi yang wajib disediakan dan diumumkan secara berkala</p>
    </div>
</div>
<div class="container mx-auto px-4 py-10" x-data="{ viewMode: 'grid' }">
    <div class="flex items-center justify-end mb-6">
        <div class="inline-flex rounded-xl border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800">
            <button
                type="button"
                class="px-3 py-1.5 text-sm rounded-lg transition"
                :class="viewMode === 'grid' ? 'bg-school-green text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                @click="viewMode = 'grid'"
            >
                Grid
            </button>
            <button
                type="button"
                class="px-3 py-1.5 text-sm rounded-lg transition"
                :class="viewMode === 'list' ? 'bg-school-green text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                @click="viewMode = 'list'"
            >
                List
            </button>
        </div>
    </div>

    @if($archives->count())
        <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($archives as $archive)
                <div class="archive-card" data-aos="fade-up">
                    <img src="{{ $archive->cover_url }}" alt="{{ $archive->title }}" class="archive-cover" loading="lazy">
                    <div class="flex-1">
                        @if($archive->category)<span class="category-badge category-badge-green text-xs mb-2 inline-block">{{ $archive->category }}</span>@endif
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 mb-2">{{ $archive->title }}</h3>
                        <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
                            <span>{{ $archive->published_date->format('d M Y') }}</span>
                            <span>{{ number_format($archive->view_count) }} dilihat</span>
                        </div>
                        <a href="{{ route('archive.view', $archive->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-school-green hover:underline">Lihat Dokumen →</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div x-show="viewMode === 'list'" class="space-y-4">
            @foreach($archives as $archive)
                <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-4 md:p-5" data-aos="fade-up">
                    <div class="flex gap-4">
                        <img src="{{ $archive->cover_url }}" alt="{{ $archive->title }}" class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-lg shrink-0" loading="lazy">
                        <div class="flex-1 min-w-0">
                            @if($archive->category)<span class="category-badge category-badge-green text-xs mb-2 inline-block">{{ $archive->category }}</span>@endif
                            <h3 class="font-semibold text-gray-900 dark:text-white text-base line-clamp-2 mb-2">{{ $archive->title }}</h3>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mb-3">
                                <span>{{ $archive->published_date->format('d M Y') }}</span>
                                <span>{{ number_format($archive->view_count) }} dilihat</span>
                            </div>
                            <a href="{{ route('archive.view', $archive->id) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-school-green hover:underline">Lihat Dokumen →</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="text-center py-16 text-gray-400">
            <p>Belum ada dokumen tersedia</p>
        </div>
    @endif

    @if($archives->hasPages())<div class="mt-8">{{ $archives->links() }}</div>@endif
</div>
@endsection
