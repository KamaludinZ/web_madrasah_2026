@props(['kop'])

{{--
    Kop khusus Halaman Portal — rata tengah: logo, nama website, tagline,
    lalu satu baris alamat & kontak. Terpisah dari layouts.header.

    $kop: name, tagline, logo (URL|null), address, mapsUrl, phone, phoneUrl,
          whatsapp (URL|null), whatsappLabel, email.
--}}
<header {{ $attributes->merge(['class' => 'text-white']) }}>
    <div class="container mx-auto px-4 py-6 md:py-8 flex flex-col items-center text-center">

        {{-- Logo --}}
        <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl bg-white p-2 shadow-xl shadow-black/20 ring-4 ring-white/20">
            @if(! empty($kop['logo']))
                <img src="{{ $kop['logo'] }}" alt="Logo {{ $kop['name'] }}" class="w-full h-full object-contain">
            @else
                <svg viewBox="0 0 60 60" class="w-full h-full" fill="none" aria-hidden="true">
                    <rect x="4" y="4" width="52" height="52" rx="12" fill="#1a7a3c"/>
                    <text x="30" y="38" text-anchor="middle" fill="white" font-size="20" font-weight="bold" font-family="Poppins">M2</text>
                </svg>
            @endif
        </div>

        {{-- Nama website & tagline --}}
        <h1 class="mt-4 text-2xl md:text-4xl font-bold font-heading leading-tight tracking-tight drop-shadow-sm">{{ $kop['name'] }}</h1>
        @if(! empty($kop['tagline']))
            <p class="mt-1 text-sm md:text-base text-green-100">{{ $kop['tagline'] }}</p>
        @endif

        {{-- Satu baris: alamat & kontak (membungkus di layar sempit) --}}
        <address class="not-italic mt-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-1.5 text-[13px] sm:text-sm text-green-50">
            @if(! empty($kop['address']))
                @if(! empty($kop['mapsUrl']))
                    <a href="{{ $kop['mapsUrl'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                @else
                    <span class="inline-flex items-center gap-1.5">
                @endif
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    {{ $kop['address'] }}
                {!! ! empty($kop['mapsUrl']) ? '</a>' : '</span>' !!}
            @endif
            @if(! empty($kop['phone']))
                <a href="{{ $kop['phoneUrl'] ?? '#' }}" class="inline-flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    {{ $kop['phone'] }}
                </a>
            @endif
            @if(! empty($kop['whatsapp']))
                <a href="{{ $kop['whatsapp'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    {{ $kop['whatsappLabel'] ?? 'WhatsApp' }}
                </a>
            @endif
            @if(! empty($kop['email']))
                <a href="mailto:{{ $kop['email'] }}" class="inline-flex items-center gap-1.5 hover:text-orange-300 transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    <span class="break-all">{{ $kop['email'] }}</span>
                </a>
            @endif
        </address>
    </div>
</header>
