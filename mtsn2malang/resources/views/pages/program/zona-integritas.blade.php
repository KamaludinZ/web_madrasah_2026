@extends('layouts.app')
@section('title', 'Zona Integritas - MTsN 2 Kota Malang')
@section('description', 'Zona Integritas MTsN 2 Kota Malang menuju Wilayah Bebas Korupsi (WBK) dan Wilayah Birokrasi Bersih Melayani (WBBM)')
@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>Program</li>
                <li>/</li>
                <li class="text-white font-medium">Zona Integritas</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Zona Integritas</h1>
        <p class="text-green-100 mt-2">Menuju Wilayah Bebas Korupsi (WBK) dan Wilayah Birokrasi Bersih Melayani (WBBM)</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto">

        {{-- ===== KONTEN FALLBACK (selalu tampil) ===== --}}
        <div class="space-y-8">

            {{-- Pengantar --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Apa itu Zona Integritas?</h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Zona Integritas (ZI) adalah predikat yang diberikan kepada instansi pemerintah yang pimpinan dan jajarannya mempunyai komitmen untuk mewujudkan WBK/WBBM melalui reformasi birokrasi, khususnya dalam hal pencegahan korupsi dan peningkatan kualitas pelayanan publik.
                        </p>
                    </div>
                </div>
            </div>

            {{-- WBK & WBBM --}}
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 border-t-4 border-school-green">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-school-green" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">WBK</h3>
                    <p class="text-sm font-semibold text-school-green mb-2">Wilayah Bebas dari Korupsi</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Predikat yang diberikan kepada unit kerja yang memenuhi sebagian besar manajemen perubahan, penataan tatalaksana, penataan sistem manajemen SDM, penguatan pengawasan, dan penguatan akuntabilitas kinerja.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 border-t-4 border-school-orange">
                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-school-orange" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">WBBM</h3>
                    <p class="text-sm font-semibold text-school-orange mb-2">Wilayah Birokrasi Bersih Melayani</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Predikat yang diberikan kepada unit kerja yang memenuhi sebagian besar manajemen perubahan, penataan tatalaksana, penataan sistem manajemen SDM, penguatan pengawasan, penguatan akuntabilitas kinerja, dan penguatan kualitas pelayanan publik.</p>
                </div>
            </div>

            {{-- 6 Area Perubahan --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    6 Area Perubahan Zona Integritas
                </h2>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach([
                        ['Manajemen Perubahan', 'Membangun budaya kerja dan pola pikir yang mendukung reformasi birokrasi'],
                        ['Penataan Tatalaksana', 'Meningkatkan efisiensi dan efektivitas sistem, proses, dan prosedur kerja'],
                        ['Penataan SDM', 'Meningkatkan profesionalisme SDM aparatur yang berintegritas'],
                        ['Penguatan Akuntabilitas', 'Meningkatkan kapasitas dan akuntabilitas kinerja instansi pemerintah'],
                        ['Penguatan Pengawasan', 'Meningkatkan penyelenggaraan pemerintahan yang bersih dan bebas KKN'],
                        ['Peningkatan Pelayanan Publik', 'Meningkatkan kualitas dan inovasi pelayanan publik'],
                    ] as $i => $area)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                        <div class="w-8 h-8 bg-school-green text-white rounded-lg flex items-center justify-center text-sm font-bold mb-3">
                            {{ $i + 1 }}
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">{{ $area[0] }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $area[1] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
        {{-- ===== END KONTEN FALLBACK ===== --}}

        {{-- ===== KONTEN DARI ADMIN (tampil di bawah jika sudah diisi) ===== --}}
        @if($content && $content->content)
        <div class="mt-10">
            <h2 class="text-2xl font-bold font-heading text-school-green mb-6">
                {{ $content->title ?: 'Informasi Tambahan' }}
            </h2>

            @if($content->image)
            <div class="mb-8 rounded-2xl overflow-hidden shadow-card">
                <img src="{{ asset('storage/' . $content->image) }}"
                     alt="{{ $content->title ?? 'Zona Integritas' }}"
                     class="w-full h-auto object-contain">
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-card p-6 md:p-8">
                <div class="prose prose-lg dark:prose-invert mx-auto
                            prose-headings:text-school-green prose-headings:font-heading
                            prose-a:text-school-green prose-a:no-underline hover:prose-a:underline
                            prose-strong:text-gray-900 dark:prose-strong:text-white">
                    {!! $content->content !!}
                </div>
            </div>
        </div>
        @endif
        {{-- ===== END KONTEN DARI ADMIN ===== --}}

        @include('partials.related-news', ['title' => 'Berita Zona Integritas'])
    </div>
</div>
@endsection
