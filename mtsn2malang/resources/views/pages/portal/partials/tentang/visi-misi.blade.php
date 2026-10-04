{{--
    Visi & Misi — tampil utuh dan berurutan (visi lalu misi), tidak dipotong.
    $visi: teks biasa (Textarea admin, baris baru dipertahankan);
    $misi: HTML rich text (daftar butir), disanitasi SafeHtml.
--}}
<x-portal-section id="tentang-visi-misi">
    @if(filled($visi))
        <h2 class="flex items-center gap-2 text-lg md:text-xl font-bold font-heading text-white mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Visi
        </h2>
        <p class="p-4 md:p-5 rounded-xl bg-white/10 border-l-4 border-school-orange text-base md:text-lg text-white font-semibold leading-relaxed whitespace-pre-line">{{ $visi }}</p>
    @endif

    @if(filled($misi))
        <h2 class="flex items-center gap-2 text-lg md:text-xl font-bold font-heading text-white {{ filled($visi) ? 'mt-8' : '' }} mb-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Misi
        </h2>
        <div class="{{ $listProse }} [&_p]:text-green-50 [&_li]:text-green-50 [&_li]:marker:text-orange-300">
            {!! \App\Support\SafeHtml::clean($misi) !!}
        </div>
    @endif
</x-portal-section>
