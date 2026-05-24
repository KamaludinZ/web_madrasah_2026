<div id="search-overlay" class="search-overlay" role="dialog" aria-label="Pencarian" aria-modal="true">
    <div class="search-box mx-4">
        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                    id="search-input"
                    placeholder="Cari berita, kategori, tag..."
                    class="flex-1 text-lg outline-none bg-transparent text-gray-900 dark:text-white placeholder-gray-400"
                    hx-get="{{ route('search.ajax') }}"
                    hx-trigger="keyup changed delay:300ms"
                    hx-target="#search-results"
                    hx-include="[name='q']"
                    name="q"
                    autocomplete="off"
                    aria-label="Kolom pencarian">
                <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">ESC</kbd>
                <button onclick="toggleSearch()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="Tutup pencarian">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div id="search-results" class="max-h-96 overflow-y-auto p-4">
            <div class="text-center py-8 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-sm">Ketik untuk mencari berita, kategori, atau tag</p>
                <p class="text-xs mt-1 text-gray-300 dark:text-gray-600">Tekan <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs">Ctrl+K</kbd> untuk membuka pencarian</p>
            </div>
        </div>

        <div class="p-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-400">
            <span>Tekan <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">Enter</kbd> untuk mencari</span>
            <a href="{{ route('search.index') }}" class="text-school-green hover:underline">Pencarian Lanjutan</a>
        </div>
    </div>
</div>
