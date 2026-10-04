{{--
    Footer ringkas Halaman Portal (pengganti footer situs di Portal):
    jam operasional, statistik pengunjung, dan ikon sosial media — rata tengah —
    lalu baris hak cipta & tautan bawah yang sama dengan footer situs.
    Jam operasional: Setting contact_hours (Pengaturan Website → Kontak Madrasah).
--}}
@php
    $hours = \App\Models\Setting::get('contact_hours', "Senin – Kamis : 07.00 – 15.00 WIB\nJumat : 07.00 – 11.00 WIB");
    $hourLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $hours))));
@endphp

<footer class="site-footer" role="contentinfo">
    <div class="container mx-auto px-4 py-8 md:py-10">
        <div class="max-w-3xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-6 text-center">

            {{-- Jam Operasional --}}
            @if($hourLines)
                <div>
                    <h2 class="flex items-center justify-center gap-2 text-sm font-semibold text-white mb-2">
                        <svg class="w-4 h-4 text-school-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Jam Operasional
                    </h2>
                    <ul class="space-y-0.5 text-sm text-green-100">
                        @foreach($hourLines as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Statistik Pengunjung --}}
            <div class="{{ $hourLines ? '' : 'sm:col-span-2' }}">
                <h2 class="flex items-center justify-center gap-2 text-sm font-semibold text-white mb-2">
                    <svg class="w-4 h-4 text-school-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Statistik Pengunjung
                </h2>
                <div class="flex justify-center gap-8 text-xs text-green-100">
                    <div>
                        <div class="text-white font-bold text-lg" data-counter="{{ \App\Models\PageView::todayCount() }}">0</div>
                        Hari Ini
                    </div>
                    <div>
                        <div class="text-white font-bold text-lg" data-counter="{{ \App\Models\PageView::totalCount() }}">0</div>
                        Total
                    </div>
                </div>
            </div>
        </div>

        {{-- Sosial Media --}}
        <x-social-links class="justify-center mt-6" item-class="w-10 h-10 bg-white/10 rounded-full" />
    </div>

    @include('layouts.footer-bottom')
</footer>
