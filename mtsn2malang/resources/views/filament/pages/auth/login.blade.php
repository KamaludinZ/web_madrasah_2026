<x-filament-panels::page.simple>
    <x-filament-panels::form id="form" wire:submit="authenticate">

        {{ $this->form }}

        {{-- Kotak kode CAPTCHA + tombol refresh --}}
        @if (! $this->otpStep)
        <div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 text-center space-y-2">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">
                Kode Keamanan
            </p>
            <x-captcha-image context="login" />
            <p class="text-xs text-gray-400 dark:text-gray-500">
                Ketik angka pada gambar di kolom "Kode Keamanan". Tidak terbaca? Tekan ↻.
            </p>
        </div>
        @else
        <div class="text-center">
            <button type="button" wire:click="cancelOtp"
                    class="text-sm text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 underline">
                ← Kembali / login dengan akun lain
            </button>
        </div>
        @endif

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page.simple>
