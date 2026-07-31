@extends('layouts.app')
@section('title', 'Sarana dan Prasarana - MTsN 2 Kota Malang')
@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Tentang Kami</li>
                <li>/</li>
                <li class="text-white font-medium">Sarpras</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Sarana dan Prasarana</h1>
        <p class="text-green-200 mt-2">Fasilitas pendukung pembelajaran dan pengembangan siswa</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($facilities as $item)
        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-card hover:shadow-card-hover transition-all duration-300 hover:-translate-y-1" data-aos="fade-up">
            @if($item->image)
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-52 object-cover">
            @else
                <div class="w-full h-52 bg-gradient-to-br from-school-green to-school-green-dark flex items-center justify-center text-white font-semibold">
                    {{ $item->name }}
                </div>
            @endif
            <div class="p-5">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $item->name }}</h3>
                @if($item->description)
                    <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3">{{ $item->description }}</p>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Fasilitas aktif MTsN 2 Kota Malang.</p>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-14">
            <p class="text-gray-500 dark:text-gray-400">Belum ada data sarana dan prasarana.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
