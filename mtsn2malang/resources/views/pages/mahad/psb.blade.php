@extends('layouts.app')

@section('title', 'Info PSB Ma\'had - MTsN 2 Kota Malang')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Ma'had</li>
                <li>/</li>
                <li class="text-white font-medium">PSB</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Info PSB Ma'had</h1>
        <p class="text-green-100 mt-2">Informasi penerimaan santri baru Ma'had MTsN 2 Kota Malang.</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-5xl mx-auto space-y-8">
        @if($registration)
            @if(!empty($registration->schedule) && is_array($registration->schedule))
                <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Jadwal Pendaftaran</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 pr-4 font-semibold text-gray-700 dark:text-gray-200">Kegiatan</th>
                                    <th class="text-left py-3 pr-4 font-semibold text-gray-700 dark:text-gray-200">Tanggal / Periode</th>
                                    <th class="text-left py-3 font-semibold text-gray-700 dark:text-gray-200">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registration->schedule as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/60">
                                        <td class="py-3 pr-4 text-gray-700 dark:text-gray-200">{{ $item['kegiatan'] ?? '-' }}</td>
                                        <td class="py-3 pr-4 text-gray-700 dark:text-gray-200">{{ $item['tanggal'] ?? '-' }}</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-300">{{ $item['keterangan'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if($registration->general_requirements)
                <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Persyaratan Umum</h2>
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        {!! $registration->general_requirements !!}
                    </div>
                </section>
            @endif

            @if($registration->specific_requirements)
                <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                    <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Persyaratan Khusus</h2>
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        {!! $registration->specific_requirements !!}
                    </div>
                </section>
            @endif

            <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-4">Dokumen & Pendaftaran</h2>
                <div class="flex flex-wrap gap-3">
                    @if($registration->brochure_file)
                        <a href="{{ asset('storage/' . $registration->brochure_file) }}" target="_blank" rel="noopener" class="btn-primary">
                            Unduh Brosur
                        </a>
                    @endif

                    @if($registration->brochure_link)
                        <a href="{{ $registration->brochure_link }}" target="_blank" rel="noopener" class="btn-secondary">
                            Lihat Brosur Online
                        </a>
                    @endif

                    @if($registration->registration_link)
                        <a href="{{ $registration->registration_link }}" target="_blank" rel="noopener" class="btn-primary">
                            PSB Online
                        </a>
                    @endif
                </div>
            </section>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-8 text-center">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-2">Informasi PSB Belum Tersedia</h2>
                <p class="text-gray-600 dark:text-gray-300">Silakan cek kembali nanti atau hubungi admin Ma'had untuk informasi terbaru.</p>
            </div>
        @endif

        @if(isset($news) && $news->count())
            <section class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                <h2 class="text-2xl font-bold font-heading text-school-green mb-6">Berita Terkait</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($news as $item)
                    <a href="{{ route('berita.show', $item->slug) }}"
                       class="group flex flex-col rounded-2xl border border-gray-100 dark:border-gray-700
                              overflow-hidden hover:border-school-green hover:shadow-md
                              transition-all duration-200 bg-white dark:bg-gray-800">

                        {{-- Gambar utama --}}
                        <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                            @if($item->thumbnail)
                                <img src="{{ asset('storage/' . $item->thumbnail) }}"
                                     alt="{{ $item->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Konten --}}
                        <div class="flex flex-col flex-1 p-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-snug
                                       group-hover:text-school-green transition-colors line-clamp-2">
                                {{ $item->title }}
                            </h3>
                            @if($item->excerpt)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 line-clamp-2 flex-1">
                                {{ $item->excerpt }}
                            </p>
                            @endif
                            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ optional($item->published_at)->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
