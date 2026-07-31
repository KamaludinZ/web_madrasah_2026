<x-filament-panels::page>
    <style>
        .fi-btn {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }
        .fi-btn:hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
        }
        .fi-btn-color-success {
            background-color: #16a34a !important;
            border-color: #16a34a !important;
            color: #ffffff !important;
        }
        .fi-btn-color-success:hover {
            background-color: #15803d !important;
            border-color: #15803d !important;
        }
        .fi-btn-color-danger {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
            color: #ffffff !important;
        }
        .fi-btn-color-danger:hover {
            background-color: #b91c1c !important;
            border-color: #b91c1c !important;
        }
    </style>
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
                        <button wire:click="disableMaintenance" class="fi-btn fi-btn-color-success fi-btn-size-md fi-btn-outlined inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-green-600 text-green-600 hover:bg-green-50 font-semibold transition-colors">
                            Aktifkan Website
                        </button>
                    @else
                        <button wire:click="enableMaintenance" class="fi-btn fi-btn-color-warning fi-btn-size-md inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 text-white hover:bg-amber-600 font-semibold transition-colors">
                            Aktifkan Maintenance
                        </button>
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
