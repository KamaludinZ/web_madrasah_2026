@extends('layouts.app')
@section('title', $archive->title . ' - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-8 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-3"><ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li class="text-white font-medium">{{ $archive->title }}</li></ol></nav>
        <h1 class="text-2xl md:text-3xl font-bold font-heading">{{ $archive->title }}</h1>
        <div class="flex items-center gap-4 mt-2 text-green-100 text-sm">
            <span>{{ $archive->published_date->format('d F Y') }}</span>
            <span>{{ number_format($archive->view_count) }} dilihat</span>
        </div>
    </div>
</div>
<div class="container mx-auto px-4 py-8">
    @if($archive->file_path)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card overflow-hidden">
        @php $ext = pathinfo($archive->file_path, PATHINFO_EXTENSION); @endphp
        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']))
        <img src="{{ $archive->file_url }}" alt="{{ $archive->title }}" class="w-full max-h-screen object-contain">
        @else
        <div class="p-4">
            <iframe src="{{ $archive->file_url }}" class="w-full h-screen rounded-xl" title="{{ $archive->title }}"></iframe>
        </div>
        @endif
    </div>
    <div class="mt-4 flex justify-center">
        <a href="{{ $archive->file_url }}" download class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Unduh Dokumen
        </a>
    </div>
    @endif
</div>
@endsection
