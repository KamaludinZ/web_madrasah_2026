{{--
    Tab Tentang — profil madrasah dari $tentang (lihat PortalController::tentangData).
    Urutan tetap: sambutan → identitas → visi & misi → tujuan, tampil utuh
    (tidak dipotong). Bagian yang kosong tidak ditampilkan, begitu pula
    tombolnya di navigasi bagian.
--}}
@php
    $sambutan = $tentang['sambutan'];
    $sections = array_filter([
        'tentang-sambutan'  => (filled($sambutan['message']) || filled($sambutan['name'])) ? 'Sambutan' : null,
        'tentang-identitas' => $tentang['identitas'] ? 'Identitas' : null,
        'tentang-visi-misi' => (filled($tentang['visi']) || filled($tentang['misi'])) ? 'Visi & Misi' : null,
        'tentang-tujuan'    => filled($tentang['tujuan']) ? 'Tujuan' : null,
    ]);
    $listProse = 'prose prose-sm prose-invert max-w-none break-words [&_img]:max-w-full [&_img]:h-auto [&_table]:block [&_table]:overflow-x-auto [&_ol]:list-decimal [&_ul]:list-disc [&_ol]:pl-5 [&_ul]:pl-5 [&_li]:mb-1.5 [&_li]:leading-relaxed';
@endphp

<div id="panel-tentang" role="tabpanel" aria-labelledby="tab-tentang" x-show="tab === 'tentang'" x-cloak tabindex="0"
     class="max-w-5xl mx-auto">

    @if(empty($sections))
        <p class="portal-glass p-6 text-center text-sm text-green-100 rounded-2xl border-dashed">
            Profil madrasah belum diisi.
        </p>
    @else
        {{-- Navigasi bagian: menggulir halus tanpa mengubah alamat halaman --}}
        @if(count($sections) > 1)
            <nav aria-label="Bagian profil madrasah" class="flex gap-2 overflow-x-auto scrollbar-hide -mx-4 px-4 sm:mx-0 sm:px-0 sm:justify-center mb-4 md:mb-6">
                @foreach($sections as $id => $label)
                    <button type="button"
                            @click="document.getElementById('{{ $id }}')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                            class="portal-glass flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-semibold text-green-50 hover:bg-white/20 hover:text-white transition-colors">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        @endif

        <div class="space-y-4 md:space-y-6">
            @isset($sections['tentang-sambutan'])
                @include('pages.portal.partials.tentang.sambutan', ['sambutan' => $sambutan])
            @endisset

            @isset($sections['tentang-identitas'])
                @include('pages.portal.partials.tentang.identitas', ['identitas' => $tentang['identitas'], 'logo' => $tentang['identitasLogo'], 'links' => $tentang['identitasLinks']])
            @endisset

            @isset($sections['tentang-visi-misi'])
                @include('pages.portal.partials.tentang.visi-misi', ['visi' => $tentang['visi'], 'misi' => $tentang['misi'], 'listProse' => $listProse])
            @endisset

            @isset($sections['tentang-tujuan'])
                @include('pages.portal.partials.tentang.tujuan', ['tujuan' => $tentang['tujuan'], 'listProse' => $listProse])
            @endisset
        </div>
    @endif
</div>
