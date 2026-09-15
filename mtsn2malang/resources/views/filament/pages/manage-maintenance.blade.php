<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Status Card --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Website</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        @if($maintenanceMode)
                            <span class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                                <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                                Website sedang dalam mode maintenance
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-green-600 dark:text-green-400">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                Website aktif dan dapat diakses publik
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    @if($maintenanceMode)
                        <x-filament::button wire:click="disableMaintenance" color="success" outlined>
                            Aktifkan Website
                        </x-filament::button>
                    @else
                        <x-filament::button wire:click="enableMaintenance" color="warning">
                            Aktifkan Maintenance
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Settings --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pengaturan Maintenance</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu Selesai Maintenance</label>
                    <input type="datetime-local" wire:model="endTime" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pesan Maintenance</label>
                    <textarea wire:model="message" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-800 dark:text-white resize-none"></textarea>
                </div>
            </div>
        </div>

        {{-- Info --}}
        <div class="fi-section rounded-xl bg-blue-50 dark:bg-blue-900/20 ring-1 ring-blue-200 dark:ring-blue-800 p-6">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">ℹ️ Informasi</h3>
            <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1">
                <li>• Saat maintenance aktif, pengunjung akan melihat halaman maintenance</li>
                <li>• Admin tetap dapat mengakses panel dengan URL: <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">/admin</code></li>
                <li>• Countdown akan ditampilkan jika waktu selesai diisi</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
