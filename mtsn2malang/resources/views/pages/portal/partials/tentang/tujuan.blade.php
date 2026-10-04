{{--
    Tujuan Madrasah — penutup rangkaian Tab Tentang.
    $tujuan: HTML rich text (daftar butir), disanitasi SafeHtml.
--}}
<x-portal-section id="tentang-tujuan">
    <h2 class="flex items-center gap-2 text-lg md:text-xl font-bold font-heading text-white mb-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
        Tujuan
    </h2>
    <div class="{{ $listProse }} [&_p]:text-green-50 [&_li]:text-green-50 [&_li]:marker:text-orange-300">
        {!! \App\Support\SafeHtml::clean($tujuan) !!}
    </div>
</x-portal-section>
