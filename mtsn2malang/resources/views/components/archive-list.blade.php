@props(['archives', 'title', 'breadcrumb'])
@extends('layouts.app')
@section('title', $title . ' - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2 flex-wrap">
            <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
            @foreach($breadcrumb as $bc)
            <li>/</li><li class="{{ $loop->last ? 'text-white font-medium' : '' }}">{{ $bc }}</li>
            @endforeach
        </ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">{{ $title }}</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($archives as $archive)
        <div class="archive-card" data-aos="fade-up">
            <img src="{{ $archive->cover_url }}" alt="{{ $archive->title }}" class="archive-cover" loading="lazy">
            <div class="flex-1">
                @if($archive->category)
                <span class="category-badge category-badge-green text-xs mb-2 inline-block">{{ $archive->category }}</span>
                @endif
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 mb-2">{{ $archive->title }}</h3>
                <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
                    <span>{{ $archive->published_date->format('d M Y') }}</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ number_format($archive->view_count) }}
                    </span>
                </div>
                <a href="{{ route('archive.view', $archive->id) }}" {{ $archive->link_type === 'link' ? 'target="_blank" rel="noopener"' : '' }}
                    class="inline-flex items-center gap-1 text-xs font-semibold text-school-green hover:text-school-green-dark transition-colors">
                    Lihat Dokumen
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-16 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p>Belum ada dokumen tersedia</p>
        </div>
        @endforelse
    </div>
    @if($archives->hasPages())
    <div class="mt-8">{{ $archives->links() }}</div>
    @endif
</div>
@endsection
