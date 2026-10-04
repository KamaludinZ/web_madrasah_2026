{{--
    Identitas Madrasah — nama, jenjang, alamat, dan keterangan pokok lain dari
    Setting identity_* (admin: Sambutan & Profil → Identitas), plus logo situs
    dan tautan profil NPSN/NSM bila diisi.
    $identitas: [label => nilai]; $logo: URL|null; $links: [label => URL].
--}}
<x-portal-section id="tentang-identitas" title="Identitas Madrasah">
    <div class="flex flex-col md:flex-row gap-5 md:gap-8 items-start">

        @if($logo)
            <figure class="flex-shrink-0 mx-auto md:mx-0">
                <div class="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 rounded-2xl bg-white ring-4 ring-white/20 shadow-lg flex items-center justify-center p-3">
                    <img src="{{ $logo }}" alt="Logo {{ $identitas['Nomenklatur'] ?? 'Madrasah' }}" class="w-full h-full object-contain" loading="lazy">
                </div>
            </figure>
        @endif

        <div class="flex-1 min-w-0 w-full">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @foreach($identitas as $label => $value)
                    <div class="p-3 bg-white/5 ring-1 ring-white/10 rounded-xl {{ in_array($label, ['Nomenklatur', 'Alamat'], true) ? 'sm:col-span-2' : '' }}">
                        <dt class="text-xs text-green-200 font-semibold uppercase tracking-wider">{{ $label }}</dt>
                        <dd class="text-sm text-white font-semibold mt-0.5 break-words">
                            @if($label === 'Website' && preg_match('#^https?://#i', $value))
                                <a href="{{ $value }}" target="_blank" rel="noopener" class="text-orange-200 hover:text-orange-100 hover:underline">{{ preg_replace('#^https?://(www\.)?#i', '', rtrim($value, '/')) }}</a>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>

            @if($links)
                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2.5 mt-4">
                    @foreach($links as $label => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-school-orange text-white hover:bg-orange-600 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            {{ $label }}
                            <span class="sr-only">(membuka tab baru)</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-portal-section>
