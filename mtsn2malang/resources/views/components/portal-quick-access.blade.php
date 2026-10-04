@props(['items'])

{{--
    Deretan tombol Akses Cepat di paling atas Tab Portal — tombol pil (ikon di
    samping label), rata tengah sehingga sedikit tombol tetap terkumpul di
    tengah. $items: daftar aktif [name, icon, url].
    Link eksternal dibuka di tab baru.
--}}
@php
    $baseUrl = url('/');
    $isExternal = fn (?string $url) => $url && preg_match('#^https?://#i', $url) && ! str_starts_with($url, $baseUrl);
@endphp

@if(count($items))
    <nav aria-label="Akses Cepat" {{ $attributes->merge(['class' => 'flex flex-wrap justify-center gap-2.5 sm:gap-3']) }}>
        @foreach($items as $item)
            @php $external = $isExternal($item['url']); @endphp
            <a href="{{ $item['url'] }}" @if($external) target="_blank" rel="noopener" @endif
               class="portal-glass portal-glass-hover group inline-flex items-center gap-2.5 sm:gap-3 pl-1.5 pr-4 sm:pr-5 py-1.5 rounded-full min-w-[8.5rem] sm:min-w-[10rem] max-w-full focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-300">
                <span class="w-9 h-9 sm:w-10 sm:h-10 flex-shrink-0 rounded-full bg-gradient-to-br from-orange-400 to-school-orange text-white flex items-center justify-center shadow-md shadow-orange-900/30 transition-transform duration-200 group-hover:scale-110">
                    <x-portal-icon :name="$item['icon']" class="w-5 h-5" />
                </span>
                <span class="text-sm font-semibold leading-tight truncate">{{ $item['name'] }}</span>
                @if($external)
                    <span class="sr-only">(membuka tab baru)</span>
                @endif
            </a>
        @endforeach
    </nav>
@endif
