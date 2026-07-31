@extends('layouts.app')
@section('title', 'Galeri Foto - PPID MTsN 2 Kota Malang')
@section('description', 'Galeri foto kegiatan MTsN 2 Kota Malang')

@section('content')
<div class="bg-gradient-to-br from-school-green-dark to-school-green py-12 text-white">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-green-200 mb-4">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white">Beranda</a></li>
                <li>/</li>
                <li>PPID</li>
                <li>/</li>
                <li class="text-white font-medium">Galeri Foto</li>
            </ol>
        </nav>
        <h1 class="text-3xl md:text-4xl font-bold font-heading">Galeri Foto</h1>
        <p class="text-green-200 mt-2">Dokumentasi foto kegiatan MTsN 2 Kota Malang</p>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    @if($galleries->count() > 0)
        <div class="space-y-4">
            @foreach($galleries as $gi => $gallery)
            <div class="gallery-group bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden" data-aos="fade-up">

                {{-- Accordion Header (klik untuk collapse/expand) --}}
                <button
                    type="button"
                    class="gallery-accordion-btn w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    onclick="toggleGalleryAccordion(this)"
                    aria-expanded="true"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex-shrink-0 w-1 h-7 bg-school-green rounded-full"></div>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold font-heading text-gray-900 dark:text-white truncate">
                                {{ $gallery->title }}
                            </h2>
                            @if($gallery->news)
                            <p class="text-xs text-gray-400 mt-0.5 truncate">
                                Dari berita:
                                <a href="{{ route('berita.show', $gallery->news->slug) }}"
                                   class="text-school-green hover:underline"
                                   onclick="event.stopPropagation()">
                                    {{ Str::limit($gallery->news->title, 60) }}
                                </a>
                                &bull; {{ $gallery->news->published_at?->format('d M Y') }}
                            </p>
                            @endif
                            @if($gallery->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $gallery->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                        <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full font-medium">
                            {{ $gallery->photos->count() }} foto
                        </span>
                        {{-- Chevron icon --}}
                        <svg class="gallery-chevron w-5 h-5 text-gray-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Accordion Body: Horizontal Scroll Strip --}}
                <div class="gallery-accordion-body border-t border-gray-100 dark:border-gray-700">
                    <div class="px-5 py-4">
                        <div class="gallery-scroll-strip flex flex-nowrap gap-2 overflow-x-auto pb-2 select-none"
                             style="scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch;"
                             oncontextmenu="return false;">
                            @foreach($gallery->photos as $index => $photo)
                            <div class="flex-shrink-0 relative group rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 cursor-pointer"
                                 style="width: 88px; height: 88px; scroll-snap-align: start;"
                                 onclick="openPpidSlideshow('{{ $gallery->id }}', {{ $index }})"
                                 oncontextmenu="return false;">
                                <img
                                    src="{{ $photo->photo_url }}"
                                    alt="{{ $gallery->title }} - Foto {{ $index + 1 }}"
                                    class="gallery-photo-protected w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                    loading="lazy"
                                    draggable="false"
                                >
                                <div class="gallery-watermark" aria-hidden="true">MTsN 2</div>
                                {{-- Hover overlay --}}
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-200 flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 w-7 h-7 bg-white/90 rounded-full flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                {{-- Index badge --}}
                                @if($index === 0)
                                <div class="absolute top-1 left-1 bg-school-green text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full leading-none">1</div>
                                @endif
                            </div>
                            @endforeach

                            {{-- Scroll hint jika foto banyak --}}
                            @if($gallery->photos->count() > 6)
                            <div class="flex-shrink-0 flex items-center justify-center text-gray-300 dark:text-gray-600 px-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            @endif
                        </div>

                        {{-- Scroll indicator dots (jika foto > 8) --}}
                        @if($gallery->photos->count() > 8)
                        <p class="text-xs text-gray-400 mt-1.5 text-right">
                            ← geser untuk lihat semua {{ $gallery->photos->count() }} foto →
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Hidden photo data for slideshow --}}
                <script>
                    window.ppidGalleries = window.ppidGalleries || {};
                    window.ppidGalleries['{{ $gallery->id }}'] = @json($gallery->photos->pluck('photo_url')->values());
                </script>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($galleries->hasPages())
        <div class="mt-10">
            {{ $galleries->links() }}
        </div>
        @endif
    @else
        <div class="text-center py-20 text-gray-400">
            <div class="text-6xl mb-4">🖼️</div>
            <p class="text-xl font-medium text-gray-500 dark:text-gray-400">Belum ada galeri foto</p>
            <p class="text-sm mt-2">Galeri foto akan ditampilkan di sini setelah ditambahkan melalui berita.</p>
        </div>
    @endif
</div>

{{-- ===== SLIDESHOW MODAL ===== --}}
<div id="ppid-gallery-slideshow" class="fixed inset-0 z-[9999] bg-black/95 hidden items-center justify-center" oncontextmenu="return false;">
    {{-- Close --}}
    <button onclick="closePpidSlideshow()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors" aria-label="Tutup">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Counter --}}
    <div class="absolute top-4 left-1/2 -translate-x-1/2 z-10 text-white text-sm bg-black/40 px-4 py-1.5 rounded-full">
        <span id="ppid-gallery-counter">1 / 1</span>
    </div>

    {{-- Prev --}}
    <button onclick="prevPpidSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors" aria-label="Sebelumnya">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    {{-- Main image --}}
    <div class="relative max-w-4xl max-h-[80vh] mx-16" oncontextmenu="return false;">
        <div class="relative rounded-xl overflow-hidden bg-black">
            <img
                id="ppid-slide-img"
                src=""
                alt=""
                class="gallery-photo-protected max-w-full max-h-[80vh] object-contain block mx-auto"
                draggable="false"
                oncontextmenu="return false;"
                style="pointer-events: none;"
            >
            <div class="gallery-watermark-slide" aria-hidden="true">MTsN 2 Kota Malang</div>
        </div>
    </div>

    {{-- Next --}}
    <button onclick="nextPpidSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors" aria-label="Berikutnya">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Thumbnail strip --}}
    <div id="ppid-thumb-strip" class="absolute bottom-4 left-0 right-0 flex justify-center gap-1.5 px-4 overflow-x-auto" oncontextmenu="return false;"></div>
</div>

<style>
    /* ===== Scroll strip ===== */
    .gallery-scroll-strip::-webkit-scrollbar {
        height: 4px;
    }
    .gallery-scroll-strip::-webkit-scrollbar-track {
        background: transparent;
    }
    .gallery-scroll-strip::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 9999px;
    }
    .dark .gallery-scroll-strip::-webkit-scrollbar-thumb {
        background: #4b5563;
    }

    /* ===== Photo protection ===== */
    .gallery-photo-protected {
        -webkit-user-drag: none;
        user-select: none;
        pointer-events: none;
    }
    .gallery-scroll-strip > div {
        pointer-events: auto !important;
    }
    .gallery-scroll-strip > div img {
        pointer-events: none;
    }

    /* ===== Watermark ===== */
    .gallery-watermark {
        position: absolute;
        inset: auto 0 0 0;
        padding: 0.2rem 0.4rem;
        font-size: 0.55rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-align: center;
        color: rgba(255, 255, 255, 0.9);
        background: linear-gradient(to top, rgba(0,0,0,0.55), rgba(0,0,0,0));
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        pointer-events: none;
    }
    .gallery-watermark-slide {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 0.4rem 0.75rem;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-align: center;
        color: rgba(255,255,255,0.9);
        background: linear-gradient(to top, rgba(0,0,0,0.6), rgba(0,0,0,0));
        text-shadow: 0 1px 3px rgba(0,0,0,0.6);
        pointer-events: none;
        border-radius: 0 0 0.75rem 0.75rem;
    }

    /* ===== Slideshow ===== */
    #ppid-gallery-slideshow { display: none; }
    #ppid-gallery-slideshow.active { display: flex; }

    /* ===== Accordion ===== */
    .gallery-accordion-body {
        overflow: hidden;
        transition: max-height 0.35s ease, opacity 0.3s ease;
        max-height: 9999px;
        opacity: 1;
    }
    .gallery-accordion-body.collapsed {
        max-height: 0 !important;
        opacity: 0;
    }
    .gallery-accordion-btn[aria-expanded="false"] .gallery-chevron {
        transform: rotate(-90deg);
    }

    /* ===== Thumbnails ===== */
    .ppid-thumb {
        flex-shrink: 0;
        width: 3rem; height: 3rem;
        border-radius: 0.5rem;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        opacity: 0.6;
        transition: all 0.2s;
    }
    .ppid-thumb.active {
        border-color: #1a7a3c;
        opacity: 1;
    }
    .ppid-thumb img {
        width: 100%; height: 100%;
        object-fit: cover;
        pointer-events: none;
    }
</style>

<script>
/* ===== Accordion ===== */
function toggleGalleryAccordion(btn) {
    const body = btn.nextElementSibling;
    const expanded = btn.getAttribute('aria-expanded') === 'true';
    if (expanded) {
        btn.setAttribute('aria-expanded', 'false');
        body.classList.add('collapsed');
    } else {
        btn.setAttribute('aria-expanded', 'true');
        body.classList.remove('collapsed');
    }
}

/* ===== Slideshow ===== */
(function() {
    let currentPhotos = [];
    let currentIndex  = 0;

    window.openPpidSlideshow = function(galleryId, index) {
        currentPhotos = window.ppidGalleries[galleryId] || [];
        currentIndex  = index;
        buildThumbs();
        updatePpidSlide();
        document.getElementById('ppid-gallery-slideshow').classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closePpidSlideshow = function() {
        document.getElementById('ppid-gallery-slideshow').classList.remove('active');
        document.body.style.overflow = '';
    };

    window.prevPpidSlide = function() {
        currentIndex = (currentIndex - 1 + currentPhotos.length) % currentPhotos.length;
        updatePpidSlide();
    };

    window.nextPpidSlide = function() {
        currentIndex = (currentIndex + 1) % currentPhotos.length;
        updatePpidSlide();
    };

    function buildThumbs() {
        const strip = document.getElementById('ppid-thumb-strip');
        strip.innerHTML = '';
        currentPhotos.forEach(function(src, i) {
            const div = document.createElement('div');
            div.className = 'ppid-thumb';
            div.dataset.index = i;
            div.onclick = function() { currentIndex = i; updatePpidSlide(); };
            div.oncontextmenu = function(e) { e.preventDefault(); return false; };
            const img = document.createElement('img');
            img.src = src;
            img.alt = 'Thumbnail ' + (i + 1);
            img.draggable = false;
            img.oncontextmenu = function(e) { e.preventDefault(); return false; };
            div.appendChild(img);
            strip.appendChild(div);
        });
    }

    function updatePpidSlide() {
        const img = document.getElementById('ppid-slide-img');
        img.src = currentPhotos[currentIndex];
        img.alt = 'Foto ' + (currentIndex + 1);
        document.getElementById('ppid-gallery-counter').textContent =
            (currentIndex + 1) + ' / ' + currentPhotos.length;
        document.querySelectorAll('.ppid-thumb').forEach(function(thumb, i) {
            thumb.classList.toggle('active', i === currentIndex);
        });
    }

    // Keyboard
    document.addEventListener('keydown', function(e) {
        const slideshow = document.getElementById('ppid-gallery-slideshow');
        if (!slideshow.classList.contains('active')) return;
        if (e.key === 'ArrowLeft')  prevPpidSlide();
        if (e.key === 'ArrowRight') nextPpidSlide();
        if (e.key === 'Escape')     closePpidSlideshow();
    });

    // Touch/swipe
    let touchStartX = 0;
    const slideshow = document.getElementById('ppid-gallery-slideshow');
    slideshow.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    slideshow.addEventListener('touchend', function(e) {
        const diff = touchStartX - e.changedTouches[0].screenX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) nextPpidSlide();
            else prevPpidSlide();
        }
    }, { passive: true });

    slideshow.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });
})();
</script>
@endsection
