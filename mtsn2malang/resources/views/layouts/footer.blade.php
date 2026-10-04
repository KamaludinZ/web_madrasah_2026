@php
    $siteName = \App\Models\Setting::get('site_name', 'MTsN 2 Kota Malang');
    $siteTagline = \App\Models\Setting::get('site_tagline', 'Madrasah Tsanawiyah Negeri 2');
    $logo = \App\Models\Setting::get('logo');
    $footerText = \App\Models\Setting::get('footer_text', 'Madrasah Tsanawiyah Negeri 2 Kota Malang — Unggul, Berkarakter, dan Berwawasan Global dalam naungan Kementerian Agama RI.');

    $topHeaderAddress = \App\Models\Setting::get('top_header_address', 'Jl. Raya Cemorokandang No. 77 Kota Malang');
    $topHeaderPhone = \App\Models\Setting::get('top_header_phone', '(0341) 711500');
    $topHeaderPhoneLink = \App\Models\Setting::get('top_header_phone_link', '0341711500');
    $topHeaderEmail = \App\Models\Setting::get('top_header_email', 'mtsnmalang2adm@gmail.com');

    $linkSippn     = \App\Models\Setting::get('link_sippn', 'https://sippn.menpan.go.id/');
    $linkPtsp      = \App\Models\Setting::get('link_ptsp', 'https://ptsp.kemenag.go.id/');
    $linkSpanLapor = \App\Models\Setting::get('link_span_lapor', 'https://www.lapor.go.id/');

    $badgeAchievements = \App\Models\BadgeAchievement::active()->get();
@endphp

<footer class="site-footer mt-16" role="contentinfo">
    {{-- Main Footer --}}
    <div class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                {{-- About --}}
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-white p-0.5 shadow-sm border border-white/30">
                            <div class="w-full h-full bg-white/10 backdrop-blur-sm rounded-[11px] overflow-hidden flex items-center justify-center">
                                @if($logo && file_exists(public_path('storage/' . $logo)))
                                    <img src="/storage/{{ $logo }}" alt="{{ $siteName }}" class="w-full h-full object-cover">
                                @else
                                    <svg viewBox="0 0 60 60" class="w-10 h-10" fill="none">
                                        <rect x="4" y="4" width="52" height="52" rx="12" fill="white" fill-opacity="0.2"/>
                                        <text x="30" y="38" text-anchor="middle" fill="white" font-size="18" font-weight="bold" font-family="Poppins">M2</text>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="font-bold text-lg font-heading">{{ $siteName }}</div>
                            <div class="text-green-100 text-xs">{{ $siteTagline }}</div>
                        </div>
                    </div>
                    <p class="text-green-100 text-sm leading-relaxed mb-4">
                        {{ $footerText }}
                    </p>
                    <x-social-links />

                    {{-- Badge Achievements --}}
                    @if($badgeAchievements->count() > 0)
                    <div class="mt-5">
                        <p class="text-xs text-green-100 mb-2 font-medium uppercase tracking-wide">Program Madrasah</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($badgeAchievements as $badge)
                            @if($badge->logo)
                            <div class="bg-white/10 hover:bg-white/20 rounded-xl p-2 transition-colors" title="{{ $badge->name }}">
                                <img src="/storage/{{ $badge->logo }}"
                                     alt="{{ $badge->name }}"
                                     class="w-16 h-16 object-contain">
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Quick Links --}}
                <div>
                    <h2 class="font-bold text-lg mb-4 font-heading">Tautan Cepat</h2>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Beranda</a></li>
                        <li><a href="{{ route('berita.index') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Berita</a></li>
                        <li><a href="{{ route('tentang.sambutan') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Sambutan Kepala</a></li>
                        <li><a href="{{ route('tentang.visi-misi') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Visi & Misi</a></li>
                        <li><a href="{{ route('tentang.daftar-gtk') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Daftar GTK</a></li>
                        <li><a href="{{ route('layanan.standar-pelayanan') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Standar Pelayanan</a></li>
                        <li><a href="{{ route('ppid.berkala') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>PPID</a></li>
                        <li><a href="{{ route('tentang.kontak') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Kontak Kami</a></li>
                    </ul>
                </div>

                {{-- Layanan --}}
                <div>
                    <h2 class="font-bold text-lg mb-4 font-heading">Layanan</h2>
                    <ul class="space-y-2">
                        <li><a href="{{ route('survei.form') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Survei Kepuasan</a></li>
                        <li><a href="{{ route('pengaduan.masyarakat') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Pengaduan Masyarakat</a></li>
                        <li><a href="{{ route('pengaduan.whistleblowing') }}" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>Whistleblowing</a></li>
                        <li><a href="{{ $linkPtsp }}" target="_blank" rel="noopener" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>PTSP Online</a></li>
                        <li><a href="{{ $linkSippn }}" target="_blank" rel="noopener" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>SIPPN</a></li>
                        <li><a href="{{ $linkSpanLapor }}" target="_blank" rel="noopener" class="text-green-100 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-school-orange rounded-full text-transparent" aria-hidden="true"></span>SPAN Lapor</a></li>
                    </ul>
                </div>

                {{-- Contact & Traffic --}}
                <div>
                    <h2 class="font-bold text-lg mb-4 font-heading">Kontak</h2>
                    <div class="space-y-3 text-sm text-green-100">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-school-orange" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            <span>{{ $topHeaderAddress }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-school-orange" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                            <a href="tel:{{ $topHeaderPhoneLink }}" class="hover:text-white transition-colors">{{ $topHeaderPhone }}</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-school-orange" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                            <a href="mailto:{{ $topHeaderEmail }}" class="hover:text-white transition-colors">{{ $topHeaderEmail }}</a>
                        </div>
                    </div>

                    {{-- Traffic Stats --}}
                    <div class="mt-6 p-4 bg-white/10 rounded-xl">
                        <h3 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-school-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Statistik Pengunjung
                        </h3>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="text-center">
                                <div class="text-white font-bold text-lg" data-counter="{{ \App\Models\PageView::todayCount() }}">0</div>
                                <div class="text-green-100">Hari Ini</div>
                            </div>
                            <div class="text-center">
                                <div class="text-white font-bold text-lg" data-counter="{{ \App\Models\PageView::totalCount() }}">0</div>
                                <div class="text-green-100">Total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    @include('layouts.footer-bottom')
</footer>
