@extends('layouts.app')

@section('title', 'MTsN 2 Kota Malang - Madrasah Tsanawiyah Negeri 2 Kota Malang')
@section('description', 'Website resmi MTsN 2 Kota Malang. Madrasah Tsanawiyah Negeri 2 Kota Malang - Unggul, Berkarakter, dan Berwawasan Global.')

@section('content')

{{-- Hero Slider --}}
<section class="hero-slider" aria-label="Banner Utama">
    <div class="hero-swiper swiper h-full">
        <div class="swiper-wrapper">
            @forelse($banners as $banner)
            <div class="swiper-slide">
                <div class="hero-slide">
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                    <div class="hero-overlay"></div>
                    <div class="absolute inset-0 flex items-center">
                        <div class="container mx-auto px-4 md:px-8">
                            <div class="max-w-2xl" data-aos="fade-right">
                                @if($banner->subtitle)
                                <span class="inline-block px-3 py-1 bg-school-orange text-white text-sm font-semibold rounded-full mb-4">{{ $banner->subtitle }}</span>
                                @endif
                                <h1 class="text-3xl md:text-5xl font-bold text-white font-heading leading-tight mb-4">{{ $banner->title }}</h1>
                                @if($banner->description)
                                <p class="text-white/80 text-lg mb-6">{{ $banner->description }}</p>
                                @endif
                                @if($banner->link)
                                <a href="{{ $banner->link }}" class="btn-secondary">
                                    Selengkapnya
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="swiper-slide">
                <div class="hero-slide bg-gradient-to-br from-school-green-dark via-school-green to-school-orange">
                    <div class="absolute inset-0 flex items-center">
                        <div class="container mx-auto px-4 md:px-8 text-center">
                            <h1 class="text-4xl md:text-6xl font-bold text-white font-heading mb-4">MTsN 2 Kota Malang</h1>
                            <p class="text-white/80 text-xl mb-8">Unggul, Berkarakter, dan Berwawasan Global</p>
                            <a href="{{ route('tentang.sambutan') }}" class="btn-secondary">Pelajari Lebih Lanjut</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next !text-white !w-10 !h-10 !bg-white/20 !rounded-full after:!text-sm"></div>
        <div class="swiper-button-prev !text-white !w-10 !h-10 !bg-white/20 !rounded-full after:!text-sm"></div>
    </div>
</section>

{{-- Quick Service Buttons --}}
<section class="py-6 bg-white dark:bg-gray-900 shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('layanan.standar-pelayanan') }}" class="flex items-center gap-2 px-5 py-3 bg-school-green text-white rounded-xl font-semibold text-sm hover:bg-school-green-dark transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Layanan
            </a>
            <a href="{{ route('survei.form') }}" class="flex items-center gap-2 px-5 py-3 bg-school-orange text-white rounded-xl font-semibold text-sm hover:bg-school-orange-dark transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Survei
            </a>
            <a href="{{ route('pengaduan.masyarakat') }}" class="flex items-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-xl font-semibold text-sm hover:bg-blue-700 transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Pengaduan
            </a>
            <a href="https://ptsp.kemenag.go.id/" target="_blank" rel="noopener" class="flex items-center gap-2 px-5 py-3 bg-purple-600 text-white rounded-xl font-semibold text-sm hover:bg-purple-700 transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                PTSP Online
            </a>
            <a href="{{ route('mahad.psb') }}" class="flex items-center gap-2 px-5 py-3 bg-teal-600 text-white rounded-xl font-semibold text-sm hover:bg-teal-700 transition-all hover:-translate-y-0.5 shadow-md" data-aos="fade-up" data-aos-delay="400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                PSB Ma'had
            </a>
        </div>
    </div>
</section>

{{-- News Section --}}
<section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="section-divider">
                <h2 class="section-title">Berita Sekolah</h2>
            </div>
            <a href="{{ route('berita.index') }}" class="text-school-green hover:text-school-green-dark font-semibold text-sm flex items-center gap-1">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Tab Navigation --}}
        <div x-data="{ activeTab: 'terbaru' }" class="mb-6">
            <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
                <button @click="activeTab = 'terbaru'" :class="activeTab === 'terbaru' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Terbaru</button>
                <button @click="activeTab = 'populer'" :class="activeTab === 'populer' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Populer</button>
                <button @click="activeTab = 'prestasi'" :class="activeTab === 'prestasi' ? 'border-school-green text-school-green' : 'border-transparent text-gray-500'" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors">Prestasi</button>
            </div>

            {{-- Terbaru --}}
            <div x-show="activeTab === 'terbaru'" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($latestNews as $news)
                    <article class="news-card" data-aos="fade-up">
                        <a href="{{ route('berita.show', $news->slug) }}" class="block overflow-hidden">
                            <img src="{{ $news->thumbnail_url }}" alt="{{ $news->title }}" class="news-card-image" loading="lazy">
                        </a>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="category-badge category-badge-green">{{ $news->category->name ?? 'Umum' }}</span>
                                <span class="text-xs text-gray-400">{{ $news->published_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-school-green transition-colors">
                                <a href="{{ route('berita.show', $news->slug) }}">{{ $news->title }}</a>
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 mb-4">{{ $news->excerpt }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $news->author->name ?? 'Admin' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ number_format($news->view_count) }}
                                </span>
                            </div>
                        </div>
                    </article>
                    @empty
                    <div class="col-span-3 text-center py-12 text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        <p>Belum ada berita</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Populer --}}
            <div x-show="activeTab === 'populer'" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($popularNews as $news)
                    <article class="news-card" data-aos="fade-up">
                        <a href="{{ route('berita.show', $news->slug) }}" class="block overflow-hidden">
                            <img src="{{ $news->thumbnail_url }}" alt="{{ $news->title }}" class="news-card-image" loading="lazy">
                        </a>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="category-badge category-badge-orange">{{ $news->category->name ?? 'Umum' }}</span>
                                <span class="text-xs text-gray-400">{{ $news->published_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-school-green transition-colors">
                                <a href="{{ route('berita.show', $news->slug) }}">{{ $news->title }}</a>
                            </h3>
                            <div class="flex items-center gap-1 text-xs text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                {{ number_format($news->view_count) }} pembaca
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>

            {{-- Prestasi --}}
            <div x-show="activeTab === 'prestasi'" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($prestasiNews as $news)
                    <article class="news-card" data-aos="fade-up">
                        <a href="{{ route('berita.show', $news->slug) }}" class="block overflow-hidden">
                            <img src="{{ $news->thumbnail_url }}" alt="{{ $news->title }}" class="news-card-image" loading="lazy">
                        </a>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="category-badge bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Prestasi</span>
                                <span class="text-xs text-gray-400">{{ $news->published_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-school-green transition-colors">
                                <a href="{{ route('berita.show', $news->slug) }}">{{ $news->title }}</a>
                            </h3>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Prestasi Section --}}
<section class="py-12 bg-gradient-to-br from-school-green-dark to-school-green text-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8" data-aos="fade-up">
            <h2 class="text-3xl font-bold font-heading mb-2">Ringkasan Prestasi</h2>
            <p class="text-green-200">Periode Tahun Pelajaran {{ \App\Models\Setting::get('academic_year', '2024/2025') }}</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-10" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['total'] ?? 0 }}">0</div>
                <div class="text-green-200 text-sm">Total Prestasi</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['regional'] ?? 0 }}">0</div>
                <div class="text-green-200 text-sm">Tingkat Regional</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['provinsi'] ?? 0 }}">0</div>
                <div class="text-green-200 text-sm">Tingkat Provinsi</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['nasional'] ?? 0 }}">0</div>
                <div class="text-green-200 text-sm">Tingkat Nasional</div>
            </div>
            <div class="stat-card">
                <div class="text-3xl font-bold mb-1" data-counter="{{ $prestasiStats['internasional'] ?? 0 }}">0</div>
                <div class="text-green-200 text-sm">Tingkat Internasional</div>
            </div>
        </div>

        {{-- Prestasi Table --}}
        <div class="bg-white/10 backdrop-blur-sm rounded-2xl overflow-hidden" data-aos="fade-up" data-aos-delay="200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-white/10">
                            <th class="px-4 py-3 text-left font-semibold">No</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama Siswa</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama Lomba</th>
                            <th class="px-4 py-3 text-left font-semibold">Tingkat</th>
                            <th class="px-4 py-3 text-left font-semibold">Juara</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prestasiList as $index => $prestasi)
                        <tr class="border-t border-white/10 hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3 text-green-200">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium">{{ $prestasi->student_name }}</td>
                            <td class="px-4 py-3 text-green-100">{{ $prestasi->competition_name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 bg-white/20 rounded-full text-xs">{{ $prestasi->level }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 bg-school-orange/80 rounded-full text-xs font-semibold">{{ $prestasi->rank }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-green-200">Belum ada data prestasi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($prestasiList->hasPages())
            <div class="p-4 border-t border-white/10">
                {{ $prestasiList->links() }}
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Sarpras Carousel --}}
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div class="section-divider">
                <h2 class="section-title">Sarana & Prasarana</h2>
            </div>
        </div>
        <div class="sarpras-swiper swiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                @foreach($sarpras as $item)
                <div class="swiper-slide">
                    <div class="rounded-2xl overflow-hidden shadow-card group">
                        <div class="relative overflow-hidden h-48">
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-4">
                                <h3 class="text-white font-semibold">{{ $item->name }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="sarpras-pagination swiper-pagination mt-4"></div>
        </div>
    </div>
</section>

{{-- Testimoni Alumni --}}
<section class="py-12 bg-gray-50 dark:bg-gray-800/50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8" data-aos="fade-up">
            <h2 class="section-title">Testimoni Alumni</h2>
            <p class="section-subtitle">Apa kata mereka tentang MTsN 2 Kota Malang</p>
        </div>
        <div class="testimoni-swiper swiper" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper-wrapper">
                @foreach($testimonials as $testimonial)
                <div class="swiper-slide">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-card h-full">
                        <div class="flex items-center gap-1 mb-4">
                            @for($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4 italic">"{{ $testimonial->content }}"</p>
                        <div class="flex items-center gap-3">
                            @if($testimonial->photo)
                            <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                            <div class="w-10 h-10 rounded-full bg-school-green flex items-center justify-center text-white font-bold text-sm">{{ substr($testimonial->name, 0, 1) }}</div>
                            @endif
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white text-sm">{{ $testimonial->name }}</div>
                                <div class="text-xs text-gray-400">Alumni {{ $testimonial->graduation_year }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="testimoni-pagination swiper-pagination mt-6"></div>
        </div>
    </div>
</section>

{{-- Portal Aplikasi --}}
@if($portalApps->count() > 0)
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8" data-aos="fade-up">
            <h2 class="section-title">Portal Aplikasi</h2>
            <p class="section-subtitle">Akses berbagai layanan digital MTsN 2 Kota Malang</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($portalApps as $app)
            <a href="{{ $app->link }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl hover:bg-green-50 dark:hover:bg-green-900/20 hover:shadow-card transition-all duration-300 hover:-translate-y-1 group" data-aos="fade-up">
                <img src="{{ $app->logo_url }}" alt="{{ $app->name }}" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">{{ $app->name }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
