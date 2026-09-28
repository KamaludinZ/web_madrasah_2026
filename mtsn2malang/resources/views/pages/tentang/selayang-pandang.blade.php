@extends('layouts.app')
@section('title', 'Selayang Pandang - MTsN 2 Kota Malang')
@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Tentang Kami</li>
                <li>/</li>
                <li class="text-white font-medium">Selayang Pandang</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Selayang Pandang</h1>
        <p class="text-green-100 mt-2">Profil dan sejarah singkat MTsN 2 Kota Malang</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">

    {{-- Konten Utama --}}
    <div class="max-w-4xl mx-auto">
        @if($content && $content->content)
            <div class="prose prose-lg dark:prose-invert mx-auto
                        prose-headings:text-school-green prose-headings:font-heading
                        prose-a:text-school-green prose-strong:text-gray-900
                        dark:prose-strong:text-white"
                 data-aos="fade-up">
                {!! \App\Support\SafeHtml::clean($content->content) !!}
            </div>
        @else
            <div class="p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-card" data-aos="fade-up">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Tentang MTsN 2 Kota Malang</h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    Madrasah Tsanawiyah Negeri 2 Kota Malang adalah lembaga pendidikan Islam negeri yang berada di bawah naungan
                    Kementerian Agama Republik Indonesia. Berlokasi di Jl. Raya Cemorokandang No. 77 Kota Malang,
                    madrasah ini telah berdiri dan berkembang menjadi salah satu madrasah unggulan di Kota Malang.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                    Konten selayang pandang dapat dikelola melalui menu <strong>Konten Halaman</strong> di panel admin.
                </p>
            </div>
        @endif
    </div>

    {{-- Riwayat Kepemimpinan --}}
    @if($headmasterHistories->isNotEmpty())
    <div class="mt-16">
        <div class="text-center mb-10" data-aos="fade-up">
            <h2 class="text-2xl md:text-3xl font-bold font-heading text-gray-900 dark:text-white">
                Riwayat Kepemimpinan
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Para kepala madrasah yang telah memimpin MTsN 2 Kota Malang</p>
            <div class="w-16 h-1 bg-school-green rounded-full mx-auto mt-4"></div>
        </div>

        <div class="relative">
            {{-- Garis timeline vertikal (desktop) --}}
            <div class="hidden md:block absolute left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-school-green via-green-300 to-transparent -translate-x-1/2 pointer-events-none"></div>

            {{-- Mobile: stack cards --}}
            <div class="md:hidden space-y-4">
                @foreach($headmasterHistories as $index => $history)
                @php $isEven = $index % 2 === 0; @endphp
                <div class="hm-card" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                    {{-- Avatar --}}
                    @if($history->photo_url)
                        <img src="{{ $history->photo_url }}" alt="{{ $history->name }}"
                             class="{{ $isEven ? 'hm-avatar-green' : 'hm-avatar-orange' }}">
                    @else
                        <div class="{{ $isEven ? 'hm-avatar-placeholder-green' : 'hm-avatar-placeholder-orange' }}">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    @endif
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <span class="{{ $isEven ? 'hm-badge-green' : 'hm-badge-orange' }}">{{ $history->period }}</span>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base leading-tight">{{ $history->name }}</h3>
                        @if($history->nip)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">NIP. {{ $history->nip }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Desktop: timeline kiri-kanan --}}
            <div class="hidden md:block">
                @foreach($headmasterHistories as $index => $history)
                @php $isEven = $index % 2 === 0; @endphp
                <div class="grid grid-cols-2 gap-8 mb-8 relative"
                     data-aos="{{ $isEven ? 'fade-right' : 'fade-left' }}"
                     data-aos-delay="{{ $index * 80 }}">

                    @if($isEven)
                        {{-- Kiri --}}
                        <div class="flex justify-end pr-10">
                            <div class="hm-card">
                                @if($history->photo_url)
                                    <img src="{{ $history->photo_url }}" alt="{{ $history->name }}" class="hm-avatar-green">
                                @else
                                    <div class="hm-avatar-placeholder-green">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="hm-badge-green">{{ $history->period }}</span>
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base leading-tight">{{ $history->name }}</h3>
                                    @if($history->nip)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">NIP. {{ $history->nip }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- Titik tengah --}}
                        <div class="hm-dot-green"></div>
                        {{-- Kanan kosong --}}
                        <div></div>
                    @else
                        {{-- Kiri kosong --}}
                        <div></div>
                        {{-- Titik tengah --}}
                        <div class="hm-dot-orange"></div>
                        {{-- Kanan --}}
                        <div class="flex justify-start pl-10">
                            <div class="hm-card">
                                @if($history->photo_url)
                                    <img src="{{ $history->photo_url }}" alt="{{ $history->name }}" class="hm-avatar-orange">
                                @else
                                    <div class="hm-avatar-placeholder-orange">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <span class="hm-badge-orange">{{ $history->period }}</span>
                                    <h3 class="font-bold text-gray-900 dark:text-white text-base leading-tight">{{ $history->name }}</h3>
                                    @if($history->nip)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">NIP. {{ $history->nip }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
