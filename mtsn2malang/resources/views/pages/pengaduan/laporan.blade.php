@extends('layouts.app')
@section('title', 'Laporan Hasil Pengaduan - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Pengaduan</li><li>/</li><li class="text-white font-medium">Laporan Hasil Pengaduan</li></ol></nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Laporan Hasil Pengaduan</h1>
        <p class="text-green-200 mt-2">Publikasi laporan hasil tindak lanjut pengaduan masyarakat</p>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($archives as $archive)
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
        @empty
        <div class="col-span-3 text-center py-16 text-gray-400"><p>Belum ada dokumen tersedia</p></div>
        @endforelse
    </div>
    @if($archives->hasPages())<div class="mt-8">{{ $archives->links() }}</div>@endif
</div>
@endsection
