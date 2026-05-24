@extends('layouts.app')
@section('title', 'Sambutan Kepala Madrasah - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2"><li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li><li>/</li><li>Tentang Kami</li><li>/</li><li class="text-white font-medium">Sambutan</li></ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Sambutan Kepala Madrasah</h1>
    </div>
</div>
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">
        @if($content)
        <div class="flex flex-col md:flex-row gap-8 items-start">
            @if($content->image)
            <div class="flex-shrink-0 text-center">
                <img src="{{ $content->image_url }}" alt="Kepala Madrasah" class="w-48 h-56 object-cover rounded-2xl shadow-card mx-auto">
                @if(isset($content->extra_data['principal_name']))
                <div class="mt-3">
                    <p class="font-bold text-gray-900 dark:text-white">{{ $content->extra_data['principal_name'] }}</p>
                    <p class="text-sm text-gray-500">Kepala MTsN 2 Kota Malang</p>
                </div>
                @endif
            </div>
            @endif
            <div class="flex-1 prose prose-lg dark:prose-invert max-w-none">
                {!! $content->content !!}
            </div>
        </div>
        @else
        <div class="prose prose-lg dark:prose-invert max-w-none">
            <p>Konten sambutan belum tersedia. Silakan tambahkan melalui panel admin.</p>
        </div>
        @endif
    </div>
</div>
@endsection
