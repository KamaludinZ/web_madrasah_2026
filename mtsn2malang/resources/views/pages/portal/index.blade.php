@extends('layouts.app')

@section('title', 'Portal - ' . $kop['name'])
@section('description', 'Portal resmi ' . $kop['name'] . ' — profil madrasah dan pintu masuk seluruh layanan.')
@section('hide_site_header', true)
@section('hide_site_footer', true)

@section('content')

{{-- ===== LATAR =====
     Satu kanvas hijau tetap di belakang seluruh halaman (running text, kop,
     isi, footer menyatu), lalu gambar latar opsional dari admin
     (Portal → Pengaturan Portal) dengan kepekatan = 100% - transparansi. --}}
<div class="portal-canvas fixed inset-0 z-0 overflow-hidden pointer-events-none" aria-hidden="true">
    @if($background['url'])
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
             style="background-image: url('{{ $background['url'] }}'); opacity: {{ $background['opacity'] }};"></div>
    @endif
    {{-- Efek pendidikan: pola ikon samar + cahaya lembut yang bergeser pelan --}}
    <div class="portal-pattern absolute inset-0"></div>
    <div class="portal-glow portal-glow--gold"></div>
    <div class="portal-glow portal-glow--green"></div>
</div>

<div class="portal-page relative z-10 min-h-screen flex flex-col text-white">

{{-- ===== RUNNING TEXT (paling atas; sumber sama dengan ticker header) ===== --}}
<x-announcement-ticker />

{{-- ===== KOP PORTAL ===== --}}
<x-portal-kop :kop="$kop" />

{{-- ===== DUA TAB UTAMA ===== --}}
{{-- Pindah tab tidak mengubah alamat halaman. Popup terjadwal sudah dirender
     oleh layouts.app (components.popup). Tab terakhir diingat per sesi
     browser (sessionStorage). Keyboard: ←/→, Home, End. --}}
<section class="flex-1 pb-10 md:pb-14"
         x-data="{
            tabs: ['portal', 'tentang'],
            tab: 'portal',
            init() {
                try {
                    const saved = sessionStorage.getItem('portal_tab');
                    if (this.tabs.includes(saved)) this.tab = saved;
                } catch (e) {}
                this.$watch('tab', value => {
                    try { sessionStorage.setItem('portal_tab', value); } catch (e) {}
                });
            },
            select(name) {
                this.tab = name;
                this.$nextTick(() => document.getElementById('tab-' + name)?.focus());
            },
            move(step) {
                const i = this.tabs.indexOf(this.tab);
                this.select(this.tabs[(i + step + this.tabs.length) % this.tabs.length]);
            },
         }">
    <div class="container mx-auto px-4">

        {{-- Pemindah tab menempel di atas saat menggulir --}}
        <div class="sticky top-0 z-30 -mx-4 px-4 pt-3 pb-3 mb-6 md:mb-10 flex justify-center">
            <div class="portal-glass flex w-full sm:w-auto p-1.5 rounded-full"
                 role="tablist" aria-label="Konten Portal"
                 @keydown.arrow-right.prevent="move(1)" @keydown.arrow-left.prevent="move(-1)"
                 @keydown.home.prevent="select(tabs[0])" @keydown.end.prevent="select(tabs[tabs.length - 1])">
                @foreach(['portal' => 'Portal', 'tentang' => 'Tentang'] as $key => $label)
                    <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}"
                            :aria-selected="(tab === '{{ $key }}').toString()"
                            :tabindex="tab === '{{ $key }}' ? 0 : -1"
                            @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-white text-school-green shadow-md' : 'text-green-50 hover:bg-white/10 hover:text-white'"
                            class="flex-1 sm:flex-none px-7 sm:px-12 py-2.5 rounded-full text-sm font-semibold tracking-wide transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-300">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ── Tab Tentang ── --}}
        @include('pages.portal.partials.tab-tentang')

        {{-- ── Tab Portal ── --}}
        @php
            // Hanya item berstatus aktif yang tampil ke pengunjung.
            $activeQuick = collect($quickAccess)->where('isActive', true)->values();
            $activeCategories = collect($categories)
                ->where('isActive', true)
                ->map(fn ($c) => array_merge($c, ['links' => collect($c['links'])->where('isActive', true)->values()->all()]))
                ->values();        @endphp
        <div id="panel-portal" role="tabpanel" aria-labelledby="tab-portal" x-show="tab === 'portal'" x-cloak tabindex="0"
             class="max-w-5xl mx-auto focus:outline-none" x-data="{ openCategory: null }" @keydown.escape.window="openCategory = null">

            {{-- Akses Cepat — tombol pil: ikon di samping label, rata tengah
                 (sedikit tombol tetap terkumpul di tengah). --}}
            <x-portal-quick-access :items="$activeQuick" class="mb-10 md:mb-14" />

            {{-- Kategori Portal --}}
            <div class="flex items-center justify-center gap-3 mb-6">
                <span class="h-px w-10 bg-white/30" aria-hidden="true"></span>
                <h2 class="text-lg md:text-xl font-bold font-heading tracking-wide">Layanan & Informasi</h2>
                <span class="h-px w-10 bg-white/30" aria-hidden="true"></span>
            </div>

            @if($activeCategories->isEmpty())
                <p class="portal-glass p-6 text-center text-sm text-green-100 rounded-2xl border-dashed">
                    Belum ada kategori portal.
                </p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($activeCategories as $i => $category)
                        <x-portal-category-card :category="$category" :index="$i" />
                    @endforeach
                </div>

                {{-- Popup daftar link anak per kategori --}}
                @foreach($activeCategories as $i => $category)
                    @continue(count($category['links']) === 0)
                    <x-portal-link-modal :category="$category" :index="$i" />
                @endforeach
            @endif
        </div>

    </div>
</section>

{{-- ===== FOOTER PORTAL (pengganti footer situs) ===== --}}
<x-portal-footer />

</div>

@endsection
