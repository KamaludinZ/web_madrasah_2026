<x-filament-panels::page.simple>
    <x-filament-panels::form id="form" wire:submit="authenticate">

        {{ $this->form }}

        {{-- Kotak kode CAPTCHA + tombol refresh --}}
        <div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 text-center space-y-2">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">
                Kode Keamanan
            </p>
            <div class="flex items-center justify-center gap-3">
                <span
                    class="font-mono text-3xl font-bold tracking-[0.5em] text-gray-900 dark:text-white select-none pl-[0.5em]"
                    wire:key="captcha-display-{{ $this->captchaCode }}"
                >{{ $this->captchaCode }}</span>
                <button
                    type="button"
                    wire:click="refreshCaptcha"
                    wire:loading.attr="disabled"
                    title="Ganti kode"
                    aria-label="Ganti kode keamanan"
                    class="p-2 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400
                           hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                >
                    <svg wire:loading.class="animate-spin" wire:target="refreshCaptcha"
                         class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500">
                Ketik kode di atas pada kolom "Kode Keamanan"
            </p>
        </div>

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page.simple>
