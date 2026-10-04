@props(['category', 'index'])

{{--
    Kartu kategori Tab Portal dengan struktur tetap agar tinggi semua kartu
    seragam: [ikon + judul maks 2 baris] / deskripsi 2 baris (tinggi tetap) /
    keterangan menempel di dasar kartu.
    - Punya link anak aktif → tombol yang membuka popup (openCategory = index,
      state Alpine milik panel Tab Portal).
    - Tanpa anak tapi ada link utama → tautan langsung.
    - Tanpa keduanya → kartu statis "Segera tersedia".
--}}
@php
    $isExternal = fn (?string $url) => $url && preg_match('#^https?://#i', $url) && ! str_starts_with($url, url('/'));
    $i = $index;
@endphp

@php
    $hasChildren = count($category['links']) > 0;
    // Struktur tetap: [ikon + judul] / deskripsi 2 baris (tinggi
    // tetap) / keterangan menempel di dasar — tinggi kartu seragam.
    $cardClass = 'portal-glass portal-glass-hover group w-full h-full text-left flex flex-col p-4 sm:p-5 rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-300';
@endphp

{{-- Kategori dengan link anak → popup; tanpa anak → link utama --}}
@if($hasChildren)
    <button type="button" class="{{ $cardClass }}" @click="openCategory = {{ $i }}" aria-haspopup="dialog">
@elseif($category['mainLink'])
    <a href="{{ $category['mainLink'] }}" class="{{ $cardClass }}" @if($isExternal($category['mainLink'])) target="_blank" rel="noopener" @endif>
@else
    <div class="{{ $cardClass }}">
@endif
    {{-- Baris atas: ikon + judul (maks 2 baris) --}}
    <span class="flex items-center gap-3.5">
        <span class="w-12 h-12 flex-shrink-0 rounded-xl bg-white/15 text-white flex items-center justify-center ring-1 ring-white/20 transition-colors group-hover:bg-school-orange group-hover:ring-orange-300/50">
            <x-portal-icon :name="$category['icon']" />
        </span>
        <span class="flex-1 min-w-0 font-bold leading-snug line-clamp-2" title="{{ $category['name'] }}">{{ $category['name'] }}</span>
    </span>

    {{-- Deskripsi: selalu 2 baris (dipotong rapi bila panjang) --}}
    <span class="block mt-3 text-sm text-green-100/90 leading-relaxed line-clamp-2 min-h-[2.75rem]"
          @if($category['description']) title="{{ $category['description'] }}" @endif>{{ $category['description'] ?: '' }}</span>

    {{-- Keterangan: selalu menempel di dasar kartu --}}
    <span class="mt-auto pt-3.5 border-t border-white/10 flex items-center justify-between text-xs font-semibold text-orange-200 group-hover:text-orange-100">
        @if($hasChildren)
            <span>{{ count($category['links']) }} tautan</span>
            <span class="inline-flex items-center gap-1">Lihat daftar
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </span>
        @elseif($category['mainLink'])
            <span>Tautan langsung</span>
            <span class="inline-flex items-center gap-1">Buka
                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        @else
            <span class="text-green-200/70">Segera tersedia</span>
        @endif
    </span>
@if($hasChildren)
    </button>
@elseif($category['mainLink'])
    </a>
@else
    </div>
@endif
