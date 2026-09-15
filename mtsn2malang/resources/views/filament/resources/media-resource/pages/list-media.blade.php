<x-filament-panels::page>
    @php
        $mediaFiles = $this->getMediaFiles();
        $images     = $mediaFiles['images'];
        $docs       = $mediaFiles['docs'];
        $galleries  = $this->getGalleryPosts();

        // Siapkan data gallery untuk JS
        $galleryJs = [];
        foreach ($galleries as $gallery) {
            if ($gallery->photos->count() > 0) {
                $photos = [];
                foreach ($gallery->photos as $pi => $photo) {
                    $photos[] = [
                        'url'  => $photo->photo_url,
                        'name' => $gallery->title . ' — foto ' . ($pi + 1),
                        'dir'  => 'gallery',
                        'size' => '',
                    ];
                }
                $galleryJs[$gallery->id] = [
                    'title'  => $gallery->title,
                    'photos' => $photos,
                ];
            }
        }

        // Siapkan data images untuk JS
        $imagesJs = [];
        foreach ($images as $img) {
            $imagesJs[] = [
                'url'  => $img['url'],
                'name' => $img['name'],
                'dir'  => $img['dir'],
                'size' => $this->formatBytes($img['size']),
            ];
        }
    @endphp

    {{-- Tab Navigation --}}
    <div class="fi-tabs flex gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1 mb-6">
        <button wire:click="setTab('gambar')"
            class="fi-tab flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-all
                {{ $activeTab === 'gambar' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            Gambar
            <span class="ml-1.5 inline-flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:text-primary-300">
                {{ count($images) }}
            </span>
        </button>
        <button wire:click="setTab('dokumen')"
            class="fi-tab flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-all
                {{ $activeTab === 'dokumen' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            Dokumen
            <span class="ml-1.5 inline-flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:text-primary-300">
                {{ count($docs) }}
            </span>
        </button>
        <button wire:click="setTab('galeri')"
            class="fi-tab flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-all
                {{ $activeTab === 'galeri' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            Galeri Foto
            <span class="ml-1.5 inline-flex items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:text-primary-300">
                {{ $galleries->count() }}
            </span>
        </button>
    </div>

    {{-- ===== TAB: GAMBAR ===== --}}
    @if($activeTab === 'gambar')
        @if(count($images) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($images as $i => $img)
            <div class="group relative rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 cursor-pointer border-2 border-transparent hover:border-primary-400 transition-all duration-200 flex-shrink-0"
                 style="width:80px;height:80px;"
                 onclick="AIS.open(window.__aisImages, {{ $i }})"
                 title="{{ $img['name'] }}">
                <img src="{{ $img['url'] }}" alt="{{ $img['name'] }}"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                     loading="lazy">
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-200 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-3">{{ count($images) }} gambar ditemukan. Klik gambar untuk melihat slideshow.</p>
        @else
        <div class="text-center py-16 text-gray-400">
            <div class="text-5xl mb-4">🖼️</div>
            <p class="text-lg font-medium">Belum ada file gambar</p>
            <p class="text-sm mt-1">Gambar akan muncul di sini setelah diupload melalui fitur berita, GTK, atau halaman lainnya.</p>
        </div>
        @endif
    @endif

    {{-- ===== TAB: DOKUMEN ===== --}}
    @if($activeTab === 'dokumen')
        @if(count($docs) > 0)
        <div class="space-y-3">
            @foreach($docs as $doc)
            @php
                $extColors = [
                    'pdf'  => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                    'doc'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                    'docx' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                    'xls'  => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                    'xlsx' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                    'ppt'  => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
                    'pptx' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
                    'zip'  => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                ];
                $extColor = $extColors[$doc['ext']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
            @endphp
            <div class="flex items-center gap-4 bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow">
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl text-sm font-bold uppercase {{ $extColor }}">
                        {{ $doc['ext'] }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-white text-sm truncate" title="{{ $doc['name'] }}">{{ $doc['name'] }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        <span>📁 {{ $doc['dir'] }}</span>
                        <span>{{ $this->formatBytes($doc['size']) }}</span>
                        <span>{{ \Carbon\Carbon::createFromTimestamp($doc['modified'])->format('d M Y') }}</span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ $doc['url'] }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-xs font-medium rounded-lg hover:bg-primary-100 dark:hover:bg-primary-800 transition-colors">
                        Buka
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16 text-gray-400">
            <div class="text-5xl mb-4">📄</div>
            <p class="text-lg font-medium">Belum ada file dokumen</p>
            <p class="text-sm mt-1">Dokumen akan muncul di sini setelah diupload melalui fitur Arsip Dokumen.</p>
        </div>
        @endif
    @endif

    {{-- ===== TAB: GALERI ===== --}}
    @if($activeTab === 'galeri')
        @if($galleries->count() > 0)
        <div class="space-y-3" x-data="{}">
            @foreach($galleries as $gallery)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm"
                 x-data="{ open: true }">

                {{-- Accordion Header --}}
                <button type="button"
                        class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        @click="open = !open" :aria-expanded="open.toString()">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex-shrink-0 w-1 h-6 bg-primary-500 rounded-full"></div>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ $gallery->title }}</h3>
                            @if($gallery->news)
                            <p class="text-xs text-gray-400 mt-0.5 truncate">
                                Dari berita:
                                <a href="{{ route('berita.show', $gallery->news->slug) }}" target="_blank"
                                   class="text-primary-600 dark:text-primary-400 hover:underline" @click.stop>
                                    {{ \Illuminate\Support\Str::limit($gallery->news->title, 55) }}
                                </a>
                            </p>
                            @endif
                            @if($gallery->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $gallery->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-xs font-semibold">
                            {{ $gallery->photos->count() }} foto
                        </span>
                        <p class="text-xs text-gray-400 hidden sm:block">{{ $gallery->created_at->format('d M Y') }}</p>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-300"
                             :class="open ? 'rotate-0' : '-rotate-90'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Accordion Body --}}
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="border-t border-gray-100 dark:border-gray-700">
                    @if($gallery->photos->count() > 0)
                    @php $gid = $gallery->id; @endphp
                    <div class="px-5 py-4">
                        <div class="ais-gallery-strip flex flex-nowrap gap-2 overflow-x-auto pb-2"
                             style="scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;">
                            @foreach($gallery->photos as $pi => $photo)
                            <div class="flex-shrink-0 group relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 cursor-pointer border-2 border-transparent hover:border-primary-400 transition-all duration-200"
                                 style="width:88px;height:88px;scroll-snap-align:start;"
                                 onclick="AIS.openGallery({{ $gid }}, {{ $pi }})"
                                 title="{{ $gallery->title }} — foto {{ $pi + 1 }}">
                                <img src="{{ $photo->photo_url }}" alt="Foto {{ $pi + 1 }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/35 transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($gallery->photos->count() > 8)
                        <p class="text-xs text-gray-400 mt-1.5 text-right">← geser untuk lihat semua {{ $gallery->photos->count() }} foto →</p>
                        @endif
                    </div>
                    @else
                    <div class="px-5 py-4 text-center text-gray-400 text-sm">Belum ada foto dalam galeri ini.</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16 text-gray-400">
            <div class="text-5xl mb-4">🖼️</div>
            <p class="text-lg font-medium">Belum ada galeri foto</p>
            <p class="text-sm mt-1">Galeri akan muncul di sini setelah ditambahkan melalui fitur Posting Galeri di halaman tambah/edit berita.</p>
        </div>
        @endif
    @endif

    {{-- ============================================================
         SLIDESHOW MODAL — render sekali, selalu ada di DOM
         ============================================================ --}}
    <div id="ais-modal" aria-modal="true" role="dialog" style="display:none;position:fixed;inset:0;z-index:99999;">
        <div id="ais-backdrop" onclick="AIS.close()" style="position:absolute;inset:0;background:rgba(0,0,0,0.78);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;overflow:hidden;">

            {{-- Top bar --}}
            <div style="flex-shrink:0;display:flex;align-items:center;gap:12px;padding:0 16px;height:56px;background:rgba(0,0,0,0.5);border-bottom:1px solid rgba(255,255,255,0.08);">
                <span id="ais-counter" style="flex-shrink:0;font-size:12px;color:rgba(255,255,255,0.7);background:rgba(255,255,255,0.1);padding:3px 10px;border-radius:999px;user-select:none;white-space:nowrap;">1 / 1</span>
                <div style="flex:1;min-width:0;text-align:center;user-select:none;">
                    <p id="ais-name" style="font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0;"></p>
                    <p id="ais-meta" style="font-size:11px;color:rgba(255,255,255,0.45);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:2px 0 0;"></p>
                </div>
                <div style="flex-shrink:0;display:flex;align-items:center;gap:8px;">
                    <a id="ais-open-link" href="#" target="_blank" onclick="event.stopPropagation()"
                       style="font-size:12px;color:rgba(255,255,255,0.8);background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);padding:5px 12px;border-radius:8px;text-decoration:none;white-space:nowrap;">
                        Buka ↗
                    </a>
                    <button onclick="AIS.close()" aria-label="Tutup"
                            style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:rgba(255,255,255,0.8);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Stage: prev + gambar + next --}}
            <div style="flex:1;min-height:0;display:flex;align-items:center;justify-content:center;padding:16px 0;overflow:hidden;">
                <button onclick="AIS.prev()" aria-label="Sebelumnya"
                        style="flex-shrink:0;width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;margin:0 16px;transition:background 0.2s,transform 0.15s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div style="flex:1;min-width:0;min-height:0;height:100%;display:flex;align-items:center;justify-content:center;">
                    <img id="ais-img" src="" alt="" draggable="false"
                         style="max-width:100%;max-height:100%;object-fit:contain;border-radius:10px;box-shadow:0 8px 48px rgba(0,0,0,0.7);display:block;user-select:none;">
                </div>
                <button onclick="AIS.next()" aria-label="Berikutnya"
                        style="flex-shrink:0;width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;margin:0 16px;transition:background 0.2s,transform 0.15s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Thumbnail strip --}}
            <div style="flex-shrink:0;height:68px;display:flex;align-items:center;justify-content:center;padding:0 16px;background:rgba(0,0,0,0.5);border-top:1px solid rgba(255,255,255,0.08);">
                <div id="ais-thumbs" style="display:flex;gap:6px;overflow-x:auto;max-width:100%;padding:4px 2px;scrollbar-width:thin;scrollbar-color:#6b7280 transparent;"></div>
            </div>
        </div>
    </div>

    <style>
    .ais-gallery-strip::-webkit-scrollbar{height:4px}
    .ais-gallery-strip::-webkit-scrollbar-track{background:transparent}
    .ais-gallery-strip::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:9999px}
    .dark .ais-gallery-strip::-webkit-scrollbar-thumb{background:#4b5563}
    #ais-thumbs::-webkit-scrollbar{height:3px}
    #ais-thumbs::-webkit-scrollbar-thumb{background:#6b7280;border-radius:9999px}
    .ais-thumb{flex-shrink:0;width:48px;height:48px;border-radius:6px;overflow:hidden;cursor:pointer;border:2px solid transparent;opacity:0.45;transition:opacity 0.2s,border-color 0.2s,transform 0.15s}
    .ais-thumb:hover{opacity:0.8;transform:scale(1.05)}
    .ais-thumb.active{border-color:#818cf8;opacity:1;transform:scale(1.08)}
    .ais-thumb img{width:100%;height:100%;object-fit:cover;pointer-events:none;user-select:none;display:block}
    </style>

    {{-- Data inject — selalu ada di DOM, diupdate tiap render Livewire --}}
    <script>
        window.__aisImages  = @json($imagesJs);
        window.__aisGallery = @json($galleryJs);
    </script>

    {{-- AIS singleton — didefinisikan sekali, tidak bergantung pada tab --}}
    <script>
    if (!window.AIS) {
        window.AIS = (function() {
            var _list = [], _cur = 0;

            function _modal()  { return document.getElementById('ais-modal'); }
            function _img()    { return document.getElementById('ais-img'); }
            function _thumbs() { return document.getElementById('ais-thumbs'); }

            function _buildThumbs() {
                var strip = _thumbs();
                strip.innerHTML = '';
                _list.forEach(function(item, i) {
                    var d = document.createElement('div');
                    d.className = 'ais-thumb';
                    d.onclick = function(e) { e.stopPropagation(); _cur = i; _update(); };
                    var im = document.createElement('img');
                    im.src = item.url; im.alt = item.name || ''; im.draggable = false;
                    d.appendChild(im); strip.appendChild(d);
                });
            }

            function _update() {
                var item = _list[_cur];
                if (!item) return;
                _img().src = item.url;
                _img().alt = item.name || '';
                document.getElementById('ais-counter').textContent = (_cur + 1) + ' / ' + _list.length;
                document.getElementById('ais-name').textContent = item.name || '';
                document.getElementById('ais-meta').textContent = item.dir ? ('📁 ' + item.dir + (item.size ? ' • ' + item.size : '')) : '';
                document.getElementById('ais-open-link').href = item.url;
                document.querySelectorAll('.ais-thumb').forEach(function(t, i) {
                    t.classList.toggle('active', i === _cur);
                });
                var active = _thumbs().children[_cur];
                if (active) active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }

            return {
                open: function(list, index) {
                    _list = list || [];
                    _cur  = index || 0;
                    _buildThumbs();
                    _update();
                    _modal().style.display = 'block';
                    document.body.style.overflow = 'hidden';
                },
                openGallery: function(galleryId, index) {
                    var gd = (window.__aisGallery || {})[galleryId];
                    if (!gd) { console.warn('AIS: gallery', galleryId, 'not found'); return; }
                    this.open(gd.photos, index);
                },
                close: function() {
                    _modal().style.display = 'none';
                    document.body.style.overflow = '';
                },
                prev: function() { _cur = (_cur - 1 + _list.length) % _list.length; _update(); },
                next: function() { _cur = (_cur + 1) % _list.length; _update(); },
            };
        })();

        // Keyboard
        document.addEventListener('keydown', function(e) {
            if (!document.getElementById('ais-modal') || document.getElementById('ais-modal').style.display === 'none') return;
            if (e.key === 'ArrowLeft')  AIS.prev();
            if (e.key === 'ArrowRight') AIS.next();
            if (e.key === 'Escape')     AIS.close();
        });

        // Swipe
        var _tx = 0;
        document.addEventListener('touchstart', function(e) {
            if (!document.getElementById('ais-modal') || document.getElementById('ais-modal').style.display === 'none') return;
            _tx = e.changedTouches[0].screenX;
        }, { passive: true });
        document.addEventListener('touchend', function(e) {
            if (!document.getElementById('ais-modal') || document.getElementById('ais-modal').style.display === 'none') return;
            var diff = _tx - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 50) { diff > 0 ? AIS.next() : AIS.prev(); }
        }, { passive: true });
    }

    // Update data setiap kali Livewire re-render (tanpa re-definisi AIS)
    window.__aisImages  = window.__aisImages  || [];
    window.__aisGallery = window.__aisGallery || {};
    </script>

</x-filament-panels::page>
