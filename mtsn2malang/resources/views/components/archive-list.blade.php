@props([
    'archives',
    'title',
    'subtitle' => '',
    'breadcrumb' => [],
    'years' => [],
])

@php
    $bulanList = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $tahunAktif = request('tahun');
    $bulanAktif = request('bulan');
    $adaFilter  = filled($tahunAktif) || filled($bulanAktif);
@endphp

<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-100 mb-4">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                @foreach($breadcrumb as $bc)
                    <li>/</li>
                    <li class="{{ $loop->last ? 'text-white font-medium' : '' }}">{{ $bc }}</li>
                @endforeach
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-green-100 mt-2">{{ $subtitle }}</p>
        @endif
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    {{-- Filter waktu --}}
    <div class="mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="tahun" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tahun</label>
                <select name="tahun" id="tahun"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm py-2 pl-3 pr-8">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected((string) $tahunAktif === (string) $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="bulan" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Bulan</label>
                <select name="bulan" id="bulan"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm py-2 pl-3 pr-8">
                    <option value="">Semua Bulan</option>
                    @foreach($bulanList as $no => $nama)
                        <option value="{{ $no }}" @selected((string) $bulanAktif === (string) $no)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 rounded-lg bg-school-green text-white text-sm font-semibold hover:bg-school-green-dark transition">
                Filter
            </button>

            @if($adaFilter)
                <a href="{{ url()->current() }}" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Reset
                </a>
            @endif
        </form>

    </div>

    @if($archives->total())
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Menampilkan {{ $archives->firstItem() }}–{{ $archives->lastItem() }} dari {{ number_format($archives->total()) }} dokumen
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($archives as $archive)
                <div class="archive-card" data-aos="fade-up">
                    <img src="{{ $archive->cover_url }}" alt="{{ $archive->title }}" class="archive-cover" loading="lazy">
                    <div class="flex-1">
                        @if($archive->category)
                            <span class="category-badge category-badge-green text-xs mb-2 inline-block">{{ $archive->category }}</span>
                        @endif
                        <h2 class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 mb-2">{{ $archive->title }}</h2>
                        <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
                            <span>{{ $archive->published_date->format('d M Y') }}</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ number_format($archive->view_count) }} dilihat
                            </span>
                        </div>
                        <a href="{{ route('archive.view', $archive->id) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-school-green hover:text-school-green-dark transition-colors">
                            Lihat Dokumen
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <div class="text-center py-16 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p>{{ $adaFilter ? 'Tidak ada dokumen pada rentang waktu tersebut.' : 'Belum ada dokumen tersedia' }}</p>
        </div>
    @endif

    @if($archives->hasPages())
        <div class="mt-8">{{ $archives->links() }}</div>
    @endif
</div>
