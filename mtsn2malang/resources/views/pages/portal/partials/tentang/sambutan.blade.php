{{--
    Sambutan Kepala Madrasah — bagian paling atas Tab Tentang, lengkap dengan
    foto dan nama. Tanpa foto: avatar inisial nama agar tata letak tetap utuh.
    $sambutan: photo (URL|null), name, period, message (HTML), motto.
--}}
@php
    $initials = collect(preg_split('/\s+/', trim(preg_replace('/,.*$/', '', (string) $sambutan['name']))))
        ->filter(fn ($w) => preg_match('/^\p{L}/u', $w) && ! preg_match('/^(drs?|hj?|ir|prof)\.?$/i', $w))
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');
@endphp

<x-portal-section id="tentang-sambutan" title="Sambutan Kepala Madrasah">
    <div class="flex flex-col md:flex-row gap-5 md:gap-8 items-start">

        {{-- Foto (atau avatar inisial) --}}
        {{-- Foto potret 3:4, fokus ke wajah (object-top) --}}
        <figure class="flex-shrink-0 mx-auto md:mx-0 w-60 sm:w-64 md:w-72 lg:w-80">
            @if($sambutan['photo'])
                <img src="{{ $sambutan['photo'] }}" alt="Foto {{ $sambutan['name'] ?: 'Kepala Madrasah' }}"
                     class="w-full aspect-[3/4] object-cover object-top rounded-2xl shadow-2xl shadow-black/30 ring-4 ring-white/20" loading="lazy">
            @else
                <div class="w-full aspect-[3/4] rounded-2xl shadow-2xl shadow-black/30 bg-white/15 ring-4 ring-white/20 text-white flex items-center justify-center"
                     role="img" aria-label="{{ $sambutan['name'] ?: 'Kepala Madrasah' }}">
                    @if($initials)
                        <span class="text-6xl md:text-7xl font-bold font-heading tracking-wide" aria-hidden="true">{{ $initials }}</span>
                    @else
                        <svg class="w-24 h-24 opacity-80" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z"/></svg>
                    @endif
                </div>
            @endif
        </figure>

        {{-- Isi sambutan → motto → nama & periode --}}
        <div class="flex-1 min-w-0">
            @if(filled($sambutan['message']))
                <div class="prose prose-sm sm:prose-base prose-invert max-w-none break-words [&_img]:max-w-full [&_img]:h-auto [&_iframe]:max-w-full text-green-50 leading-relaxed">
                    {!! \App\Support\SafeHtml::clean($sambutan['message']) !!}
                </div>
            @endif

            @if(filled($sambutan['motto']))
                <blockquote class="mt-5 border-l-4 border-school-orange pl-3 md:pl-4 text-sm md:text-base italic text-orange-100 font-medium">
                    {{ $sambutan['motto'] }}
                </blockquote>
            @endif

            @if(filled($sambutan['name']) || filled($sambutan['period']))
                <div class="mt-6 pt-4 border-t border-white/15">
                    @if(filled($sambutan['name']))
                        <p class="font-bold text-white">{{ $sambutan['name'] }}</p>
                    @endif
                    <p class="text-sm text-green-200">{{ $sambutan['period'] ?: 'Kepala Madrasah' }}</p>
                </div>
            @endif
        </div>
    </div>
</x-portal-section>
