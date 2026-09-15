@php
    $siteName = \App\Models\Setting::get('site_name', 'MTsN 2 Kota Malang');
    $siteTagline = \App\Models\Setting::get('site_tagline', 'Madrasah Tsanawiyah Negeri 2');
    $logo = \App\Models\Setting::get('logo');
    $footerText = \App\Models\Setting::get('footer_text', 'Madrasah Tsanawiyah Negeri 2 Kota Malang — Unggul, Berkarakter, dan Berwawasan Global dalam naungan Kementerian Agama RI.');

    $topHeaderAddress = \App\Models\Setting::get('top_header_address', 'Jl. Raya Cemorokandang No. 77 Kota Malang');
    $topHeaderPhone = \App\Models\Setting::get('top_header_phone', '(0341) 711500');
    $topHeaderPhoneLink = \App\Models\Setting::get('top_header_phone_link', '0341711500');
    $topHeaderEmail = \App\Models\Setting::get('top_header_email', 'mtsnmalang2adm@gmail.com');

    $socialYoutube   = \App\Models\Setting::get('social_youtube', 'https://www.youtube.com/channel/UCsqzV9zkTBlYZfmOA-_Y63A');
    $socialInstagram = \App\Models\Setting::get('social_instagram', 'https://www.instagram.com/mtsn2kota_malang');
    $socialFacebook  = \App\Models\Setting::get('social_facebook', 'https://web.facebook.com/mtsn2kotamalang');
    $socialTiktok    = \App\Models\Setting::get('social_tiktok', 'https://www.tiktok.com/@mtsn2kotamalang');
    $socialWaChannel = \App\Models\Setting::get('social_whatsapp_channel', '');

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
                    <div class="flex gap-3">
                        <a href="{{ $socialYoutube }}" target="_blank" rel="noopener" class="w-9 h-9 bg-white/10 hover:bg-red-500 rounded-lg flex items-center justify-center transition-colors" aria-label="YouTube">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <a href="{{ $socialInstagram }}" target="_blank" rel="noopener" class="w-9 h-9 bg-white/10 hover:bg-pink-500 rounded-lg flex items-center justify-center transition-colors" aria-label="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" class="w-9 h-9 bg-white/10 hover:bg-blue-600 rounded-lg flex items-center justify-center transition-colors" aria-label="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="{{ $socialTiktok }}" target="_blank" rel="noopener" class="w-9 h-9 bg-white/10 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors" aria-label="TikTok">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </a>
                        @if($socialWaChannel)
                        <a href="{{ $socialWaChannel }}" target="_blank" rel="noopener" class="w-9 h-9 bg-white/10 hover:bg-green-500 rounded-lg flex items-center justify-center transition-colors" aria-label="WhatsApp Channel">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        @endif
                    </div>

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
    <div class="border-t border-green-700 py-4">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-3 text-sm text-green-100">
            <p>&copy; {{ date('Y') }} MTsN 2 Kota Malang. Hak Cipta Dilindungi.</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Beranda</a>
                <a href="{{ route('tentang.kontak') }}" class="hover:text-white transition-colors">Kontak</a>
                <a href="{{ route('layanan.maklumat') }}" class="hover:text-white transition-colors">Maklumat</a>
                <a href="{{ route('survei.form') }}" class="hover:text-white transition-colors">Survei</a>
            </div>
        </div>
    </div>
</footer>
