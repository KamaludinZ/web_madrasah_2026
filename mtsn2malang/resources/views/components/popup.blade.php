@php
    use App\Models\Popup;
    try {
        $activePopups = Popup::active()->with('items')->get();
    } catch (\Throwable $e) {
        $activePopups = collect();
    }
@endphp

@if($activePopups->isNotEmpty())
@foreach($activePopups as $popup)
@if($popup->items->isNotEmpty())

{{-- ═══════════════════════════════════════════════════════════════
     POPUP: {{ $popup->title }}  (ID: {{ $popup->id }}, type: {{ $popup->type }})
     ═══════════════════════════════════════════════════════════════ --}}
<div
    id="popup-overlay-{{ $popup->id }}"
    class="popup-overlay fixed inset-0 z-[9999] flex items-center justify-center p-4"
    style="background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); display:none;"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $popup->title }}"
>
    <div
        id="popup-box-{{ $popup->id }}"
        class="popup-box relative w-full rounded-2xl shadow-2xl"
        style="max-width: {{ $popup->type === 'slider' ? '860px' : '640px' }};"
    >
        {{-- ── Tombol Tutup ── --}}
        <button
            onclick="closePopup({{ $popup->id }})"
            class="absolute top-3 right-3 z-10 w-9 h-9 flex items-center justify-center rounded-full bg-black/50 hover:bg-black/80 text-white transition-colors"
            aria-label="Tutup popup"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        @if($popup->type === 'slider')
        {{-- ════════════════════════════════════════
             MODE SLIDER GAMBAR
             ════════════════════════════════════════ --}}
        <div class="bg-black rounded-2xl overflow-hidden">
            <div id="popup-swiper-{{ $popup->id }}" class="swiper" style="width:100%;overflow:hidden;">
                <div class="swiper-wrapper">
                    @foreach($popup->items as $item)
                    @if($item->image)
                    <div class="swiper-slide" style="position:relative;">
                        @if($item->image_link)
                        <a href="{{ $item->image_link }}" target="_blank" rel="noopener" onclick="closePopup({{ $popup->id }})">
                        @endif
                            <img
                                src="{{ asset('storage/' . $item->image) }}"
                                alt="{{ $item->caption ?: $popup->title }}"
                                class="w-full object-cover block"
                                style="max-height:75vh;width:100%;"
                            >
                        @if($item->image_link)
                        </a>
                        @endif
                        @if($item->caption)
                        <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top,rgba(0,0,0,0.7),transparent);padding:1rem 1.25rem;">
                            <p style="color:#fff;font-size:0.875rem;font-weight:500;margin:0;">{{ $item->caption }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
                @if($popup->items->count() > 1)
                <div id="popup-pagination-{{ $popup->id }}" class="swiper-pagination"></div>
                <div id="popup-prev-{{ $popup->id }}" class="swiper-button-prev"></div>
                <div id="popup-next-{{ $popup->id }}" class="swiper-button-next"></div>
                @endif
            </div>
        </div>

        @else
        {{-- ════════════════════════════════════════
             MODE TEKS / PENGUMUMAN
             ════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-school-green-dark to-school-green px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white/70 text-xs font-medium uppercase tracking-wider">Pengumuman</p>
                        <h2 class="text-white font-bold text-lg leading-tight">{{ $popup->title }}</h2>
                    </div>
                </div>
            </div>

            {{-- Konten --}}
            <div class="px-6 py-5 max-h-[60vh] overflow-y-auto">
                @foreach($popup->items as $item)
                @if($item->content)
                <div class="prose prose-sm dark:prose-invert max-w-none
                            prose-headings:text-gray-900 dark:prose-headings:text-white
                            prose-p:text-gray-700 dark:prose-p:text-gray-300
                            prose-a:text-school-green prose-a:no-underline hover:prose-a:underline
                            prose-strong:text-gray-900 dark:prose-strong:text-white
                            prose-ul:text-gray-700 dark:prose-ul:text-gray-300
                            prose-ol:text-gray-700 dark:prose-ol:text-gray-300">
                    {!! \App\Support\SafeHtml::clean($item->content) !!}
                </div>
                @endif
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <button
                    onclick="closePopup({{ $popup->id }})"
                    class="px-5 py-2 bg-school-green hover:bg-school-green-dark text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
        @endif

    </div>{{-- /popup-box --}}
</div>{{-- /popup-overlay --}}

@endif {{-- items not empty --}}
@endforeach {{-- popups --}}

@php
    $popupJsonData = $activePopups->map(function($p) {
        return [
            'id'        => $p->id,
            'type'      => $p->type,
            'show_once' => (bool) $p->show_once,
            'items'     => $p->items->count(),
        ];
    })->values();
@endphp
@push('scripts')
<script>
(function () {
    var allPopups = @json($popupJsonData);
    var swiperMap = {};
    var queue     = [];

    // Key sessionStorage menyertakan tanggal hari ini
    // → show_once hanya berlaku dalam satu sesi tab (tutup tab = tampil lagi)
    // → key lama (tanpa tanggal) dibersihkan otomatis
    var today = (new Date()).toISOString().slice(0, 10);
    function closedKey(id) { return 'popup_closed_' + id + '_' + today; }

    // Bersihkan key lama (format lama tanpa tanggal) agar tidak memblok
    try {
        allPopups.forEach(function(p) {
            sessionStorage.removeItem('popup_closed_' + p.id);
        });
    } catch(e) {}

    // ── Bangun antrian berdasarkan urutan server (sudah diurutkan by order) ─
    function buildQueue() {
        queue = [];
        allPopups.forEach(function (p) {
            if (p.items === 0) return;
            if (p.show_once) {
                try { if (sessionStorage.getItem(closedKey(p.id))) return; } catch(e) {}
            }
            queue.push(p);
        });
    }

    // ── Tampilkan popup berikutnya dari antrian ─────────────────────────────
    function showNext() {
        if (queue.length === 0) return;
        showPopup(queue.shift());
    }

    // ── Tampilkan satu popup ────────────────────────────────────────────────
    function showPopup(p) {
        var overlay = document.getElementById('popup-overlay-' + p.id);
        if (!overlay) { showNext(); return; }

        // Tampilkan overlay
        overlay.style.display = 'flex';

        // Animasi masuk
        var box = document.getElementById('popup-box-' + p.id);
        if (box) {
            box.style.opacity    = '0';
            box.style.transform  = 'scale(0.9) translateY(20px)';
            box.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    box.style.opacity   = '1';
                    box.style.transform = 'scale(1) translateY(0)';
                });
            });
        }

        // Init Swiper via window.initPopupSwiper (dari app.js, sudah include modul)
        // Delay 100ms agar elemen benar-benar visible & dimensi terhitung
        if (p.type === 'slider' && p.items >= 2 && !swiperMap[p.id]) {
            setTimeout(function() {
                if (typeof window.initPopupSwiper === 'function') {
                    var sw = window.initPopupSwiper(p.id, p.items);
                    if (sw) swiperMap[p.id] = sw;
                }
            }, 100);
        }

        // Tutup saat klik area gelap (overlay, bukan box)
        function overlayClickHandler(e) {
            if (e.target === overlay) {
                overlay.removeEventListener('click', overlayClickHandler);
                closePopup(p.id);
            }
        }
        overlay.addEventListener('click', overlayClickHandler);

        // Tutup dengan Escape
        function escHandler(e) {
            if (e.key === 'Escape') {
                document.removeEventListener('keydown', escHandler);
                closePopup(p.id);
            }
        }
        document.addEventListener('keydown', escHandler);
    }

    // ── Tutup popup (dipanggil dari tombol onclick di HTML) ─────────────────
    window.closePopup = function(id) {
        var overlay = document.getElementById('popup-overlay-' + id);
        var box     = document.getElementById('popup-box-' + id);
        if (!overlay) return;

        // Animasi keluar
        if (box) {
            box.style.opacity   = '0';
            box.style.transform = 'scale(0.9) translateY(20px)';
        }

        setTimeout(function() {
            overlay.style.display = 'none';

            // Simpan ke sessionStorage jika show_once (key berbasis tanggal)
            var p = allPopups.find(function(x) { return x.id === id; });
            if (p && p.show_once) {
                try { sessionStorage.setItem(closedKey(id), '1'); } catch(e) {}
            }

            // Destroy Swiper jika ada
            if (swiperMap[id]) {
                try { swiperMap[id].destroy(true, true); } catch(e) {}
                delete swiperMap[id];
            }

            // Tampilkan popup berikutnya setelah jeda singkat
            setTimeout(showNext, 350);
        }, 320);
    };

    // ── Entry point ─────────────────────────────────────────────────────────
    function run() {
        buildQueue();
        if (queue.length === 0) return;
        setTimeout(showNext, 900);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
</script>
@endpush
@endif
