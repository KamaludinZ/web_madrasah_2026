<div class="accessibility-widget" x-data="{ open: false }" role="complementary" aria-label="Widget Aksesibilitas">
    {{-- Toggle Button --}}
    <button @click="open = !open"
        class="accessibility-toggle"
        :aria-expanded="open"
        aria-label="Buka panel aksesibilitas"
        title="Aksesibilitas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
    </button>

    {{-- Panel --}}
    <div class="accessibility-panel" :class="{ 'open': open }" role="dialog" aria-label="Pengaturan Aksesibilitas">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Aksesibilitas</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" aria-label="Tutup">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Font Size --}}
        <div class="mb-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wider">Ukuran Teks</p>
            <div class="flex items-center gap-2">
                <button onclick="accessibilityManager.decreaseFontSize()"
                    class="flex-1 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors font-bold"
                    aria-label="Perkecil teks">A-</button>
                <button onclick="accessibilityManager.resetFontSize()"
                    class="flex-1 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                    aria-label="Reset ukuran teks">Reset</button>
                <button onclick="accessibilityManager.increaseFontSize()"
                    class="flex-1 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors font-bold"
                    aria-label="Perbesar teks">A+</button>
            </div>
        </div>

        {{-- Display Options --}}
        <div class="mb-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wider">Tampilan</p>
            <div class="space-y-2">
                <button onclick="accessibilityManager.toggleHighContrast()"
                    class="w-full flex items-center gap-3 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors text-left"
                    aria-label="Toggle kontras tinggi">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Kontras Tinggi</span>
                </button>

                <button onclick="accessibilityManager.toggleGrayscale()"
                    class="w-full flex items-center gap-3 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors text-left"
                    aria-label="Toggle grayscale">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Grayscale</span>
                </button>

                <button onclick="accessibilityManager.toggleDyslexiaFont()"
                    class="w-full flex items-center gap-3 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors text-left"
                    aria-label="Toggle font disleksia">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Font Disleksia</span>
                </button>

                <button onclick="accessibilityManager.toggleUnderlineLinks()"
                    class="w-full flex items-center gap-3 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors text-left"
                    aria-label="Toggle garis bawah tautan">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span class="text-gray-700 dark:text-gray-200">Garis Bawah Tautan</span>
                </button>
            </div>
        </div>

        {{-- Mode --}}
        <div class="mb-4">
            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wider">Mode Tampilan</p>
            <div class="grid grid-cols-3 gap-1">
                <button onclick="themeManager.apply('light')" class="py-2 text-xs bg-white border border-gray-200 hover:border-school-green rounded-lg transition-colors text-gray-700" aria-label="Mode terang">☀️ Terang</button>
                <button onclick="themeManager.apply('dark')" class="py-2 text-xs bg-gray-800 border border-gray-600 hover:border-school-green rounded-lg transition-colors text-white" aria-label="Mode gelap">🌙 Gelap</button>
                <button onclick="themeManager.apply('system')" class="py-2 text-xs bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:border-school-green rounded-lg transition-colors text-gray-700 dark:text-gray-200" aria-label="Mode sistem">💻 Sistem</button>
            </div>
        </div>

        {{-- Reset --}}
        <button onclick="accessibilityManager.reset()"
            class="w-full py-2 text-sm text-center text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
            aria-label="Reset semua pengaturan aksesibilitas">
            Reset Semua Pengaturan
        </button>

        <p class="text-xs text-gray-400 dark:text-gray-500 mt-3 text-center">
            Sesuai standar WCAG 2.1 AA
        </p>
    </div>
</div>
