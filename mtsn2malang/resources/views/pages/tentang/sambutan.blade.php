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
@php
    $headmasterPhoto = \App\Models\Setting::get('headmaster_photo');
    $headmasterName = \App\Models\Setting::get('headmaster_name');
    $headmasterNip = \App\Models\Setting::get('headmaster_nip');
    $headmasterPeriod = \App\Models\Setting::get('headmaster_period');

    $headmasterImageUrl = $headmasterPhoto
        ? asset('storage/' . $headmasterPhoto)
        : ($content?->image ? $content->image_url : null);
@endphp
<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col md:flex-row gap-8 items-start">
            <div class="flex-shrink-0 text-center w-full md:w-auto">
                @if($headmasterImageUrl)
                    <img
                        src="{{ $headmasterImageUrl }}"
                        alt="{{ $headmasterName ?: 'Kepala Madrasah' }}"
                        class="w-48 h-56 object-cover rounded-2xl shadow-card mx-auto"
                    >
                @else
                    <div class="w-48 h-56 rounded-2xl shadow-card mx-auto bg-gradient-to-br from-school-green to-school-green-dark flex items-center justify-center text-white">
                        <div class="text-center px-4">
                            <div class="w-16 h-16 rounded-full bg-white/20 mx-auto mb-3 flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.121 17.804A9.97 9.97 0 0112 15c2.2 0 4.234.711 5.879 1.915M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold">Foto Kepala Madrasah</p>
                            <p class="text-xs text-white/80 mt-1">Belum diunggah</p>
                        </div>
                    </div>
                @endif

                <div class="mt-3 space-y-1">
                    @if($headmasterName)
                        <p class="font-bold text-gray-900 dark:text-white">{{ $headmasterName }}</p>
                    @elseif(isset($content?->extra_data['principal_name']))
                        <p class="font-bold text-gray-900 dark:text-white">{{ $content->extra_data['principal_name'] }}</p>
                    @else
                        <p class="font-bold text-gray-900 dark:text-white">Kepala MTsN 2 Kota Malang</p>
                    @endif

                    <p class="text-sm text-gray-500">Kepala MTsN 2 Kota Malang</p>

                    @if($headmasterNip)
                        <p class="text-sm text-gray-600 dark:text-gray-300">NIP. {{ $headmasterNip }}</p>
                    @endif

                    @if($headmasterPeriod)
                        <p class="text-xs text-gray-500 dark:text-gray-400">Periode: {{ $headmasterPeriod }}</p>
                    @endif
                </div>
            </div>

            <div class="flex-1 prose prose-lg dark:prose-invert max-w-none">
                @if($content)
                    {!! $content->content !!}
                @else
                    <p>Konten sambutan belum tersedia. Silakan tambahkan melalui panel admin.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
