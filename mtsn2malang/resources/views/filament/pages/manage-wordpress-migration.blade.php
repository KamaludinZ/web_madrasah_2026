<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB NAVIGATION
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="border-b border-gray-200 dark:border-gray-700 mb-6">
    <nav class="flex gap-1 -mb-px" aria-label="Tabs">
        @foreach([
            ['key' => 'online',   'label' => 'Migrasi Online',   'icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'],
            ['key' => 'offline',  'label' => 'Migrasi Offline',  'icon' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3'],
            ['key' => 'rollback', 'label' => 'Rollback & Monitor', 'icon' => 'M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3'],
        ] as $tab)
        <button
            type="button"
            wire:click="switchTab('{{ $tab['key'] }}')"
            @class([
                'flex items-center gap-2 px-5 py-3 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap',
                'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
                    => $activeTab === $tab['key'],
                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300'
                    => $activeTab !== $tab['key'],
                'text-danger-600 dark:text-danger-400 hover:text-danger-700'
                    => $tab['key'] === 'rollback' && $activeTab !== 'rollback',
                'border-danger-600 text-danger-600 dark:text-danger-400'
                    => $tab['key'] === 'rollback' && $activeTab === 'rollback',
            ])
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
            </svg>
            {{ $tab['label'] }}
        </button>
        @endforeach
    </nav>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     QUEUE STATS & STATUS BANNER
═══════════════════════════════════════════════════════════════════════════ --}}
@php
    $runningBatch  = \App\Models\WordpressMigrationBatch::where('status', 'running')->latest()->first();
@endphp

{{-- Banner: Ada batch RUNNING (jarang terjadi karena sync, tapi tampilkan jika ada) --}}
@if($runningBatch)
<div class="rounded-xl border border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-4 flex items-center gap-4 mb-4">
    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
    </svg>
    <div class="flex-1">
        <p class="font-semibold text-blue-800 dark:text-blue-300 text-sm">
            🔄 Batch #{{ $runningBatch->id }} sedang diproses ({{ ucfirst($runningBatch->mode) }})
        </p>
        <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">
            Dimulai: {{ $runningBatch->started_at?->format('d M Y H:i:s') }}
        </p>
    </div>
</div>
@endif

{{-- Placeholder agar tidak ada perubahan struktur blade di bawah --}}
@if(false)
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB 1 — MIGRASI ONLINE
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'online')
<div class="space-y-6" wire:key="tab-online">

    {{-- Info box --}}
    <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 text-sm text-blue-800 dark:text-blue-300">
        <p class="font-semibold mb-1">ℹ️ Migrasi Online</p>
        <p>Koneksi langsung ke database MySQL WordPress. Pastikan server hosting WordPress mengizinkan koneksi remote dari IP server ini.</p>
    </div>

    {{-- Form --}}
    <x-filament::section>
        <x-slot name="heading">Konfigurasi Koneksi & Opsi</x-slot>
        {{ $this->onlineForm }}
    </x-filament::section>

    {{-- Preview Stats --}}
    @if(!empty($previewStats))
    <x-filament::section>
        <x-slot name="heading">Preview Data WordPress</x-slot>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(['posts' => 'Posts', 'categories' => 'Kategori', 'tags' => 'Tags', 'attachments' => 'Lampiran'] as $key => $label)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($previewStats[$key] ?? 0) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </x-filament::section>
    @endif

    {{-- Hasil --}}
    @if($onlineResult)
    <div @class([
        'rounded-xl border p-4 text-sm font-mono whitespace-pre-wrap',
        'border-green-300 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300' => $onlineResultType === 'success',
        'border-red-300 bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300' => $onlineResultType !== 'success',
    ])>{{ $onlineResult }}</div>
    @endif

    {{-- Tombol Aksi --}}
    <div class="flex flex-wrap gap-3">
        <x-filament::button
            wire:click="previewOnline"
            wire:loading.attr="disabled"
            color="gray"
            icon="heroicon-o-magnifying-glass"
        >
            <span wire:loading.remove wire:target="previewOnline">Preview Data</span>
            <span wire:loading wire:target="previewOnline">Mengambil preview...</span>
        </x-filament::button>

        <x-filament::button
            wire:click="runOnlineMigration"
            wire:loading.attr="disabled"
            wire:confirm="Yakin ingin menjalankan migrasi? Pastikan Dry Run sudah dinonaktifkan untuk migrasi nyata."
            color="primary"
            icon="heroicon-o-arrow-path"
        >
            <span wire:loading.remove wire:target="runOnlineMigration">Jalankan Migrasi Online</span>
            <span wire:loading wire:target="runOnlineMigration">Memproses...</span>
        </x-filament::button>
    </div>

</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB 2 — MIGRASI OFFLINE
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'offline')
<div class="space-y-6" wire:key="tab-offline">

    {{-- Info box --}}
    <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-sm text-amber-800 dark:text-amber-300">
        <p class="font-semibold mb-1">📁 Migrasi Offline</p>
        <p>Migrasi dari database lokal atau file XML (WXR export WordPress). Cocok jika tidak ada akses remote ke database WordPress.</p>
    </div>

    {{-- Form --}}
    <x-filament::section>
        <x-slot name="heading">Konfigurasi Sumber Data Offline</x-slot>
        {{ $this->offlineForm }}
    </x-filament::section>

    {{-- Hasil --}}
    @if($offlineResult)
    <div @class([
        'rounded-xl border p-4 text-sm font-mono whitespace-pre-wrap',
        'border-green-300 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300' => $offlineResultType === 'success',
        'border-red-300 bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300' => $offlineResultType !== 'success',
    ])>{{ $offlineResult }}</div>
    @endif

    {{-- Tombol Aksi --}}
    <div class="flex flex-wrap gap-3">
        <x-filament::button
            wire:click="previewOffline"
            wire:loading.attr="disabled"
            color="gray"
            icon="heroicon-o-magnifying-glass"
        >
            <span wire:loading.remove wire:target="previewOffline">Preview Data</span>
            <span wire:loading wire:target="previewOffline">Mengambil preview...</span>
        </x-filament::button>

        <x-filament::button
            wire:click="runOfflineMigration"
            wire:loading.attr="disabled"
            wire:confirm="Yakin ingin menjalankan migrasi offline? Pastikan Dry Run sudah dinonaktifkan untuk migrasi nyata."
            color="primary"
            icon="heroicon-o-arrow-path"
        >
            <span wire:loading.remove wire:target="runOfflineMigration">Jalankan Migrasi Offline</span>
            <span wire:loading wire:target="runOfflineMigration">Memproses...</span>
        </x-filament::button>
    </div>

</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB 3 — ROLLBACK & MONITOR
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeTab === 'rollback')
<div class="space-y-6" wire:key="tab-rollback">

    {{-- Ringkasan Batch --}}
    @php
        $totalBatches    = \App\Models\WordpressMigrationBatch::count();
        $completedBatches = \App\Models\WordpressMigrationBatch::where('status', 'completed')->count();
        $failedBatches   = \App\Models\WordpressMigrationBatch::where('status', 'failed')->count();
        $runningBatches  = \App\Models\WordpressMigrationBatch::where('status', 'running')->count();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $totalBatches }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Batch</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold {{ $runningBatches > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}">{{ $runningBatches }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sedang Berjalan</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold {{ $completedBatches > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">{{ $completedBatches }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selesai</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
            <p class="text-2xl font-bold {{ $failedBatches > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">{{ $failedBatches }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gagal</p>
        </div>
    </div>

    {{-- Info mode sync --}}
    <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4 text-sm text-green-800 dark:text-green-300">
        <p class="font-semibold mb-1">✅ Mode Eksekusi Langsung (Sync)</p>
        <p>Migrasi dijalankan langsung saat tombol ditekan — tidak memerlukan queue worker. Proses mungkin memakan waktu beberapa menit tergantung jumlah data. Jangan tutup browser selama proses berlangsung.</p>
    </div>

    {{-- Warning --}}
    <div class="rounded-xl border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-800 dark:text-red-300">
        <p class="font-semibold mb-1">⚠️ Perhatian — Rollback Tidak Dapat Dibatalkan</p>
        <p>Rollback akan <strong>menghapus permanen</strong> semua berita yang diimpor dari batch migrasi terakhir. Pastikan Anda sudah yakin sebelum melanjutkan.</p>
    </div>

    {{-- Hasil rollback --}}
    @if($rollbackResult)
    <div @class([
        'rounded-xl border p-4 text-sm font-mono',
        'border-green-300 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300' => $rollbackResultType === 'success',
        'border-red-300 bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300' => $rollbackResultType !== 'success',
    ])>{{ $rollbackResult }}</div>
    @endif

    {{-- Tombol Rollback --}}
    <x-filament::section>
        <x-slot name="heading">Rollback Batch Terakhir</x-slot>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Hanya batch migrasi terakhir yang berstatus <strong>completed</strong> yang akan di-rollback.
            Berita yang dihapus tidak dapat dipulihkan.
        </p>
        <x-filament::button
            wire:click="rollbackLastBatch"
            wire:loading.attr="disabled"
            wire:confirm="PERINGATAN: Semua berita dari batch migrasi terakhir akan dihapus permanen. Lanjutkan?"
            color="danger"
            icon="heroicon-o-arrow-uturn-left"
        >
            <span wire:loading.remove wire:target="rollbackLastBatch">Rollback Batch Migrasi Terakhir</span>
            <span wire:loading wire:target="rollbackLastBatch">Memproses rollback...</span>
        </x-filament::button>
    </x-filament::section>

    {{-- Riwayat Batch --}}
    <x-filament::section>
        <x-slot name="heading">Riwayat Batch Migrasi</x-slot>

        @php $batches = $this->batchHistory; @endphp

        @if($batches->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">Belum ada riwayat migrasi.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
                            <th class="pb-3 pr-3 font-semibold text-gray-700 dark:text-gray-300">#</th>
                            <th class="pb-3 pr-3 font-semibold text-gray-700 dark:text-gray-300">Mode</th>
                            <th class="pb-3 pr-3 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="pb-3 pr-3 font-semibold text-gray-700 dark:text-gray-300">Posts</th>
                            <th class="pb-3 pr-3 font-semibold text-gray-700 dark:text-gray-300">Gambar</th>
                            <th class="pb-3 pr-3 font-semibold text-gray-700 dark:text-gray-300">Waktu</th>
                            <th class="pb-3 pr-3 font-semibold text-gray-700 dark:text-gray-300">Catatan / Error</th>
                            <th class="pb-3 font-semibold text-gray-700 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($batches as $batch)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="py-3 pr-3 text-gray-500 dark:text-gray-400 font-mono">{{ $batch->id }}</td>
                            <td class="py-3 pr-3">
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $batch->mode === 'online',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $batch->mode === 'offline',
                                ])>{{ ucfirst($batch->mode) }}</span>
                            </td>
                            <td class="py-3 pr-3">
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                    'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'   => $batch->status === 'completed',
                                    'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'           => $batch->status === 'failed',
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'          => in_array($batch->status, ['rolled_back', 'cancelled']),
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'       => $batch->status === 'running',
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' => $batch->status === 'queued',
                                ])>
                                    @if($batch->status === 'running')
                                    <svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    @endif
                                    {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                </span>
                            </td>
                            <td class="py-3 pr-3 font-mono text-gray-900 dark:text-white">{{ number_format($batch->migrated_posts) }}</td>
                            <td class="py-3 pr-3 font-mono text-gray-900 dark:text-white">{{ number_format($batch->handled_images) }}</td>
                            <td class="py-3 pr-3 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                <div>{{ $batch->started_at?->format('d M Y H:i') ?? '-' }}</div>
                                @if($batch->finished_at)
                                <div class="text-gray-400">→ {{ $batch->finished_at->format('H:i:s') }}</div>
                                @endif
                            </td>
                            <td class="py-3 pr-3 text-gray-500 dark:text-gray-400 max-w-[200px]">
                                @if($batch->notes)
                                <details>
                                    <summary class="text-xs cursor-pointer hover:text-gray-700 dark:hover:text-gray-200 truncate max-w-[180px]"
                                             title="{{ $batch->notes }}">
                                        {{ Str::limit($batch->notes, 50) }}
                                    </summary>
                                    <div class="mt-1 text-xs bg-gray-100 dark:bg-gray-800 rounded p-2 whitespace-pre-wrap break-all max-w-xs max-h-32 overflow-y-auto">{{ $batch->notes }}</div>
                                </details>
                                @else
                                <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    {{-- Tombol Cancel untuk queued/running --}}
                                    @if(in_array($batch->status, ['queued', 'running']))
                                    <x-filament::button
                                        wire:click="cancelBatch({{ $batch->id }})"
                                        wire:confirm="Batalkan Batch #{{ $batch->id }}?"
                                        color="danger"
                                        size="xs"
                                        icon="heroicon-o-x-circle"
                                    >
                                        Batalkan
                                    </x-filament::button>
                                    @endif

                                    {{-- Tombol Retry untuk failed/cancelled --}}
                                    @if(in_array($batch->status, ['failed', 'cancelled']))
                                    <x-filament::button
                                        wire:click="retryBatch({{ $batch->id }})"
                                        wire:confirm="Jalankan ulang migrasi berdasarkan konfigurasi Batch #{{ $batch->id }}?"
                                        color="warning"
                                        size="xs"
                                        icon="heroicon-o-arrow-path"
                                    >
                                        Retry
                                    </x-filament::button>
                                    @endif

                                    {{-- Tombol Force-fail untuk running yang stuck --}}
                                    @if($batch->status === 'running' && $batch->started_at?->diffInMinutes(now()) > 35)
                                    <x-filament::button
                                        wire:click="forceCompleteBatch({{ $batch->id }})"
                                        wire:confirm="Tandai Batch #{{ $batch->id }} sebagai failed? (Gunakan jika job stuck > 35 menit)"
                                        color="gray"
                                        size="xs"
                                    >
                                        Force Fail
                                    </x-filament::button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

</div>
@endif

</x-filament-panels::page>
