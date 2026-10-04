@props(['category', 'index'])

{{--
    Popup daftar link anak sebuah kategori Portal. Dibuka/ditutup lewat state
    Alpine `openCategory` milik panel Tab Portal (openCategory === $index).
    Tutup: tombol ×, klik latar, atau Escape (ditangani panel). x-trap mengunci
    fokus di dalam popup dan mengembalikannya ke kartu kategori saat ditutup.

    $category: name, icon, links[] — tiap link: name, icon, description, url
               (hanya link aktif yang dikirim ke komponen ini).
--}}
@php
    $baseUrl = url('/');
    $isExternal = fn (?string $url) => $url && preg_match('#^https?://#i', $url) && ! str_starts_with($url, $baseUrl);

    // Alamat tujuan ringkas: path untuk link internal, domain untuk eksternal.
    $displayUrl = function (?string $url) use ($baseUrl, $isExternal) {
        if (blank($url) || $url === '#') {
            return null;
        }
        if ($isExternal($url)) {
            return preg_replace('#^www\.#', '', (string) parse_url($url, PHP_URL_HOST));
        }

        return '/' . ltrim(\Illuminate\Support\Str::after($url, $baseUrl), '/');
    };

    $titleId = 'portal-category-' . $index . '-title';
@endphp

<div x-show="openCategory === {{ $index }}" x-cloak
     class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4"
     role="dialog" aria-modal="true" aria-labelledby="{{ $titleId }}">

    {{-- Latar --}}
    <div class="absolute inset-0 bg-black/50" @click="openCategory = null"
         x-show="openCategory === {{ $index }}" x-transition.opacity></div>

    {{-- Panel --}}
    <div class="relative w-full sm:max-w-lg max-h-[85vh] flex flex-col overflow-hidden bg-white dark:bg-gray-900 rounded-t-2xl sm:rounded-2xl shadow-2xl ring-1 ring-black/5"
         x-show="openCategory === {{ $index }}"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-trap.noscroll="openCategory === {{ $index }}">

        {{-- Kepala popup: hijau seperti Portal; daftar tetap terang agar mudah dibaca --}}
        <div class="flex items-center gap-3 p-5 bg-gradient-to-br from-school-green to-school-green-dark text-white">
            <span class="w-11 h-11 flex-shrink-0 rounded-xl bg-gradient-to-br from-orange-400 to-school-orange text-white flex items-center justify-center shadow-md shadow-black/20">
                <x-portal-icon :name="$category['icon']" class="w-5 h-5" />
            </span>
            <div class="flex-1 min-w-0">
                <h3 id="{{ $titleId }}" class="font-bold font-heading leading-snug">{{ $category['name'] }}</h3>
                <p class="text-xs text-green-100">{{ count($category['links']) }} tautan</p>
            </div>
            <button type="button" @click="openCategory = null"
                    class="p-2 rounded-lg text-green-100 hover:text-white hover:bg-white/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-300"
                    aria-label="Tutup daftar {{ $category['name'] }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <ul class="overflow-y-auto overscroll-contain p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] space-y-1">
            @foreach($category['links'] as $link)
                @php
                    $external = $isExternal($link['url']);
                    $shown = $displayUrl($link['url']);
                @endphp
                <li>
                    <a href="{{ $link['url'] }}" @if($external) target="_blank" rel="noopener" @endif
                       class="group flex items-start gap-3 p-3 rounded-xl hover:bg-green-50 dark:hover:bg-green-900/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-school-green transition-colors">
                        <span class="w-9 h-9 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-800 text-school-green flex items-center justify-center group-hover:bg-white dark:group-hover:bg-gray-900">
                            <x-portal-icon :name="$link['icon']" class="w-5 h-5" />
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $link['name'] }}
                                @if($external)
                                    <span class="sr-only">(membuka tab baru)</span>
                                    <svg class="inline w-3 h-3 ml-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                @endif
                            </span>
                            @if(filled($link['description']))
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $link['description'] }}</span>
                            @endif
                            <span class="flex flex-wrap items-center gap-x-2 mt-1 text-[11px] text-gray-400">
                                <span>{{ $category['name'] }}</span>
                                @if($shown)
                                    <span aria-hidden="true">·</span>
                                    <span class="truncate max-w-[12rem] sm:max-w-[16rem]">{{ $shown }}</span>
                                @endif
                            </span>
                        </span>
                        <svg class="w-4 h-4 mt-2.5 flex-shrink-0 text-gray-300 group-hover:text-school-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
