<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB NAVIGATION UTAMA (murni Livewire, tanpa Alpine entangle)
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="mb-6 overflow-x-auto">
    <nav
        class="inline-flex min-w-full gap-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-1.5"
        aria-label="Main Tabs"
    >
        @foreach([
            ['key' => 'settings', 'label' => 'Pengaturan',       'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['key' => 'log',      'label' => 'Monitoring Log',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['key' => 'audit',    'label' => 'Audit Aktivitas',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['key' => 'backup',   'label' => 'Backup & Restore', 'icon' => 'M5 8h14M5 8a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v1a2 2 0 01-2 2M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
            ['key' => 'update',   'label' => 'Update Aplikasi',  'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
        ] as $tab)
        <button
            type="button"
            wire:click="$set('activeMainTab', '{{ $tab['key'] }}')"
            @class([
                'flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-semibold whitespace-nowrap transition-all',
                'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700'
                    => $activeMainTab === $tab['key'],
                'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-900/40'
                    => $activeMainTab !== $tab['key'],
            ])
        >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/></svg>
            {{ $tab['label'] }}
        </button>
        @endforeach
    </nav>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB: PENGATURAN (form utama)
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeMainTab === 'settings')
<div wire:key="tab-settings">
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB: MONITORING LOG
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeMainTab === 'log')
@php $logData = $this->getLogContent(); @endphp
<div class="space-y-4" wire:key="tab-log" wire:poll.30000ms="$refresh">

    {{-- Header & Controls --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Monitoring Log Aplikasi</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                File: <code class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">storage/logs/laravel.log</code>
                @if($logData['exists'])
                    &nbsp;·&nbsp; Ukuran: <strong>{{ number_format($logData['size'] / 1024, 1) }} KB</strong>
                    &nbsp;·&nbsp; Menampilkan: <strong>{{ $logData['count'] }}</strong> entri
                @endif
                &nbsp;·&nbsp; <span class="text-green-600 dark:text-green-400">Auto-refresh 30 detik</span>
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Filter Level --}}
            <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 text-xs font-medium">
                @foreach(['all' => 'Semua', 'error' => 'Error', 'warning' => 'Warning', 'info' => 'Info', 'debug' => 'Debug'] as $key => $label)
                <button
                    type="button"
                    wire:click="setLogFilter('{{ $key }}')"
                    @class([
                        'px-3 py-1.5 transition-colors',
                        'bg-primary-600 text-white' => $logFilter === $key,
                        'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' => $logFilter !== $key,
                        'border-l border-gray-300 dark:border-gray-600' => $key !== 'all',
                    ])
                >{{ $label }}</button>
                @endforeach
            </div>

            {{-- Jumlah baris --}}
            <select
                wire:change="setLogLines($event.target.value)"
                class="text-xs border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300"
            >
                @foreach([50 => '50 entri', 100 => '100 entri', 200 => '200 entri', 500 => '500 entri'] as $n => $label)
                <option value="{{ $n }}" @selected($logLines == $n)>{{ $label }}</option>
                @endforeach
            </select>

            {{-- Tombol Refresh --}}
            <x-filament::button wire:click="refreshLog" color="gray" size="sm">
                Refresh
            </x-filament::button>

            {{-- Tombol Clear --}}
            <x-filament::button
                wire:click="clearLog"
                wire:confirm="Yakin ingin menghapus semua isi log? Tindakan ini tidak dapat dibatalkan."
                color="danger"
                size="sm"
            >
                Bersihkan Log
            </x-filament::button>
        </div>
    </div>

    @if(! $logData['exists'])
    <div class="rounded-xl border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 p-6 text-center text-yellow-800 dark:text-yellow-300">
        <p class="font-semibold">File log tidak ditemukan</p>
        <p class="text-sm mt-1">storage/logs/laravel.log belum ada. Log akan muncul setelah ada aktivitas di aplikasi.</p>
    </div>
    @elseif(empty($logData['lines']))
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-6 text-center text-gray-500 dark:text-gray-400">
        <p class="font-semibold">Tidak ada entri log</p>
        <p class="text-sm mt-1">Tidak ada log yang cocok dengan filter "{{ $logFilter }}".</p>
    </div>
    @else
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-950 overflow-hidden">
        <div class="overflow-y-auto max-h-[600px] p-4 space-y-1 font-mono text-xs">
            @foreach($logData['lines'] as $entry)
            @php
                $entryUpper = strtoupper($entry);
                $isError   = str_contains($entryUpper, '.ERROR:') || str_contains($entryUpper, ' ERROR ');
                $isWarning = str_contains($entryUpper, '.WARNING:') || str_contains($entryUpper, ' WARNING ');
                $isInfo    = str_contains($entryUpper, '.INFO:') || str_contains($entryUpper, ' INFO ');
                $isDebug   = str_contains($entryUpper, '.DEBUG:') || str_contains($entryUpper, ' DEBUG ');
            @endphp
            <div @class([
                'px-3 py-2 rounded border-l-4 whitespace-pre-wrap break-all leading-relaxed',
                'border-red-500 bg-red-950/40 text-red-300'         => $isError,
                'border-yellow-500 bg-yellow-950/40 text-yellow-300' => $isWarning && !$isError,
                'border-blue-500 bg-blue-950/40 text-blue-300'       => $isInfo && !$isError && !$isWarning,
                'border-gray-600 bg-gray-900/40 text-gray-400'       => $isDebug && !$isError && !$isWarning && !$isInfo,
                'border-gray-700 bg-gray-900/20 text-gray-500'       => !$isError && !$isWarning && !$isInfo && !$isDebug,
            ])>{{ $entry }}</div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB: AUDIT AKTIVITAS
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeMainTab === 'audit')
@php $auditData = $this->getAuditLogs(); @endphp
<div class="space-y-4" wire:key="tab-audit">

    {{-- Header & Controls --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Audit Aktivitas Admin</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Total: <strong>{{ number_format($auditData['total']) }}</strong> aktivitas tercatat
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Search --}}
            <input
                type="text"
                wire:model.live.debounce.400ms="auditSearch"
                placeholder="Cari aktivitas atau user..."
                class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 w-56"
            />

            {{-- Filter Log Name --}}
            <select
                wire:model.live="auditLogName"
                class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300"
            >
                <option value="">Semua Kategori</option>
                @foreach($this->getAuditLogNames() as $logName)
                <option value="{{ $logName }}">{{ ucfirst($logName) }}</option>
                @endforeach
            </select>

            {{-- Clear Audit --}}
            <x-filament::button
                wire:click="clearAuditLog"
                wire:confirm="Yakin ingin menghapus semua audit log? Tindakan ini tidak dapat dibatalkan."
                color="danger"
                size="sm"
            >
                Bersihkan Audit
            </x-filament::button>
        </div>
    </div>

    @if($auditData['records']->isEmpty())
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-10 text-center text-gray-500 dark:text-gray-400">
        <p class="font-semibold">Belum ada aktivitas tercatat</p>
        <p class="text-sm mt-1">Aktivitas admin akan muncul di sini setelah ada perubahan data.</p>
    </div>
    @else
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">User</th>
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Kategori</th>
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Event</th>
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Deskripsi</th>
                        <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Subject</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($auditData['records'] as $activity)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                            <div>{{ $activity->created_at->format('d M Y') }}</div>
                            <div class="font-mono">{{ $activity->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($activity->causer)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-700 dark:text-primary-300 text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($activity->causer->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white text-xs">{{ $activity->causer->name ?? '-' }}</div>
                                    <div class="text-gray-400 text-xs">{{ $activity->causer->email ?? '' }}</div>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400 text-xs italic">Sistem</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $logColors = [
                                    'user'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'news'    => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                    'default' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                ];
                                $logColor = $logColors[$activity->log_name] ?? $logColors['default'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $logColor }}">
                                {{ ucfirst($activity->log_name ?? 'default') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $eventColors = [
                                    'created' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    'updated' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    'deleted' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                ];
                                $eventColor = $eventColors[$activity->event] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
                            @endphp
                            @if($activity->event)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $eventColor }}">
                                {{ ucfirst($activity->event) }}
                            </span>
                            @else
                            <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs">
                            <div class="truncate" title="{{ $activity->description }}">{{ $activity->description }}</div>
                            @if($activity->properties && $activity->properties->isNotEmpty())
                            <details class="mt-1">
                                <summary class="text-xs text-primary-600 dark:text-primary-400 cursor-pointer hover:underline">Lihat detail</summary>
                                <pre class="mt-1 text-xs bg-gray-100 dark:bg-gray-800 rounded p-2 overflow-x-auto max-w-xs">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                            @if($activity->subject_type)
                            <div class="font-mono">{{ class_basename($activity->subject_type) }}</div>
                            <div class="text-gray-400">#{{ $activity->subject_id }}</div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($auditData['lastPage'] > 1)
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/40">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Halaman <strong>{{ $auditData['currentPage'] }}</strong> dari <strong>{{ $auditData['lastPage'] }}</strong>
                &nbsp;·&nbsp; Total <strong>{{ number_format($auditData['total']) }}</strong> aktivitas
            </p>
            <div class="flex gap-2">
                <x-filament::button
                    wire:click="auditPrevPage"
                    :disabled="$auditData['currentPage'] <= 1"
                    color="gray"
                    size="sm"
                >
                    &larr; Sebelumnya
                </x-filament::button>
                <x-filament::button
                    wire:click="auditNextPage"
                    :disabled="$auditData['currentPage'] >= $auditData['lastPage']"
                    color="gray"
                    size="sm"
                >
                    Berikutnya &rarr;
                </x-filament::button>
            </div>
        </div>
        @endif
    </div>
    @endif

</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB: BACKUP & RESTORE
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeMainTab === 'backup')
@php
    $backups = $this->getBackups();
    $restores = $this->getRestores();
    $backupBusy = $this->hasRunningBackup();
    $restoreBusy = $this->hasRunningRestore();
@endphp
<div class="space-y-6" wire:key="tab-backup" wire:poll.{{ ($backupBusy || $restoreBusy) ? '3s' : '15s' }}>
    <h2 class="sr-only">Backup & Restore Aplikasi</h2>

    {{-- Versi aplikasi --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Versi Aplikasi Saat Ini</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Dipakai sebagai label pada tiap backup yang dibuat.</p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-mono font-bold">
            v{{ $this->currentAppVersion() }}
        </span>
    </div>

    {{-- ── Backup ── --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Backup Aplikasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Backup penuh: database + seluruh file media (storage) + file <code class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">.env</code>.
                    Simpan file backup di tempat aman — berisi kredensial database.
                </p>
            </div>
            <x-filament::button wire:click="createBackup" :disabled="$backupBusy" icon="heroicon-o-archive-box-arrow-down">
                {{ $backupBusy ? 'Sedang Membuat Backup...' : 'Buat Backup Sekarang' }}
            </x-filament::button>
        </div>

        @if($backupBusy)
        <div class="mb-4 flex items-center gap-2 text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-4 py-3">
            <svg class="w-4 h-4 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Sedang membuat backup di background. Halaman ini otomatis memperbarui status tiap beberapa detik.
        </div>
        @endif

        @if($backups->isEmpty())
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-6 text-center text-gray-500 dark:text-gray-400">
            <p class="font-semibold">Belum ada backup</p>
            <p class="text-sm mt-1">Buat backup pertama Anda di atas.</p>
        </div>
        @else
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr class="text-left">
                        <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300">File</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300">Tipe</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300">Ukuran</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300">Versi</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300">Waktu</th>
                        <th class="px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($backups as $backup)
                    <tr wire:key="backup-{{ $backup->id }}">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $backup->filename ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span @class([
                                'inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $backup->type === 'manual',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' => $backup->type === 'pre_restore',
                                'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300' => $backup->type === 'pre_update',
                            ])>
                                {{ match($backup->type) { 'manual' => 'Manual', 'pre_restore' => 'Sebelum Restore', 'pre_update' => 'Sebelum Update', default => $backup->type } }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $backup->formattedSize() }}</td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 font-mono text-xs">v{{ $backup->app_version ?? '?' }}</td>
                        <td class="px-4 py-2.5">
                            <span @class([
                                'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $backup->status === 'running',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' => $backup->status === 'completed',
                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $backup->status === 'failed',
                            ])>
                                @if($backup->status === 'running')<svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>@endif
                                {{ match($backup->status) { 'running' => 'Berjalan', 'completed' => 'Selesai', 'failed' => 'Gagal', default => $backup->status } }}
                            </span>
                            @if($backup->status === 'failed' && $backup->error)
                            <p class="text-xs text-red-500 mt-1 max-w-xs truncate" title="{{ $backup->error }}">{{ $backup->error }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ $backup->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if($backup->status === 'completed')
                                <button wire:click="downloadBackup({{ $backup->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors" title="Unduh">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </button>
                                @endif
                                @if($backup->status !== 'running')
                                <button wire:click="deleteBackup({{ $backup->id }})" wire:confirm="Hapus backup ini secara permanen?" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Restore ── --}}
    <div class="rounded-xl border-2 border-red-200 dark:border-red-900/50 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-bold text-red-700 dark:text-red-400 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Restore Aplikasi
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">
            Menimpa <strong>seluruh database dan file media saat ini</strong> dengan isi backup yang diunggah.
            Tindakan ini tidak bisa dibatalkan di tengah jalan. Sistem otomatis membuat backup pengaman dari
            kondisi saat ini sebelum mulai menimpa apa pun.
        </p>

        @if($restoreBusy)
        <div class="mb-4 flex items-center gap-2 text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-4 py-3">
            <svg class="w-4 h-4 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Restore sedang berjalan. Jangan tutup atau reload halaman ini.
        </div>
        @else
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[240px]">
                <label for="restore-file-input" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">File Backup (.zip)</label>
                <input id="restore-file-input" type="file" wire:model="restoreFile" accept=".zip"
                       class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-red-50 file:text-red-700 dark:file:bg-red-900/30 dark:file:text-red-300 hover:file:bg-red-100 border border-gray-300 dark:border-gray-600 rounded-lg">
                @error('restoreFile') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div wire:loading wire:target="restoreFile" class="text-xs text-gray-400 mt-1">Mengunggah...</div>
            </div>
            <x-filament::button color="danger" wire:click="openRestoreConfirm" :disabled="! $restoreFile">
                Lanjutkan Restore
            </x-filament::button>
        </div>
        @endif

        {{-- Modal konfirmasi --}}
        @if($showRestoreConfirmModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60"
             wire:click.self="cancelRestoreConfirm"
             x-data
             x-trap.inert.noscroll="true"
             x-on:keydown.escape.window="$wire.cancelRestoreConfirm()">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6"
                 role="dialog" aria-modal="true" aria-labelledby="restore-confirm-title">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 id="restore-confirm-title" class="text-lg font-bold text-gray-900 dark:text-white">Konfirmasi Restore</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Anda akan menimpa <strong>seluruh database dan file media saat ini</strong> dengan isi file
                    <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ $restoreFile?->getClientOriginalName() }}</span>.
                    Ketik <strong class="font-mono">RESTORE</strong> untuk melanjutkan.
                </p>
                <label for="restore-confirm-text" class="sr-only">Ketik RESTORE untuk konfirmasi</label>
                <input id="restore-confirm-text" type="text" wire:model.live="restoreConfirmText" placeholder="Ketik RESTORE"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm font-mono mb-4 focus:border-red-500 focus:ring-red-500">
                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="cancelRestoreConfirm">Batal</x-filament::button>
                    <x-filament::button color="danger" wire:click="runRestore" :disabled="$restoreConfirmText !== 'RESTORE'">
                        Ya, Timpa & Restore Sekarang
                    </x-filament::button>
                </div>
            </div>
        </div>
        @endif

        @if($restores->isNotEmpty())
        <div class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Riwayat Restore</p>
            <div class="space-y-2">
                @foreach($restores as $r)
                <div class="flex items-center justify-between text-sm bg-gray-50 dark:bg-gray-800/50 rounded-lg px-3 py-2" wire:key="restore-{{ $r->id }}">
                    <div class="min-w-0">
                        <p class="font-mono text-xs text-gray-700 dark:text-gray-300 truncate">{{ $r->source_filename }}</p>
                        <p class="text-xs text-gray-400">{{ $r->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span @class([
                        'inline-flex px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0 ml-2',
                        'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $r->status === 'running',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' => $r->status === 'completed',
                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $r->status === 'failed',
                    ])>
                        {{ match($r->status) { 'running' => 'Berjalan', 'completed' => 'Selesai', 'failed' => 'Gagal', default => $r->status } }}
                    </span>
                </div>
                @if($r->status === 'failed' && $r->error)
                <p class="text-xs text-red-500 px-3">{{ $r->error }}</p>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB: UPDATE APLIKASI
═══════════════════════════════════════════════════════════════════════════ --}}
@if($activeMainTab === 'update')
@php
    $updateHistory = $this->getUpdateHistory();
    $updateBusy = $this->hasRunningUpdate();
    // Backup pengaman otomatis (pre_update) dibuat SEBELUM status job "update"
    // ada — ikut poll saat backup/restore lain berjalan juga supaya tab ini
    // tidak tampak diam padahal ada proses terkait yang sedang berjalan.
    $anyMaintenanceBusy = $updateBusy || $this->hasRunningBackup() || $this->hasRunningRestore();
    $hasToken = $this->hasGithubToken();
@endphp
<div class="space-y-6" wire:key="tab-update" wire:poll.{{ $anyMaintenanceBusy ? '3s' : '15s' }}>
    <h2 class="sr-only">Update Aplikasi</h2>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Versi Terpasang</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Dibaca dari file <code class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">VERSION</code> di root aplikasi.</p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-sm font-mono font-bold">
            v{{ $this->currentAppVersion() }}
        </span>
    </div>

    {{-- Konfigurasi GitHub --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Sumber Update (GitHub)</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Update diambil dari GitHub Releases repo ini. Repo privat butuh Personal Access Token dengan izin <code class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">repo</code>.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="update-github-repo" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Repository (pemilik/nama-repo)</label>
                <input id="update-github-repo" type="text" wire:model="updateGithubRepo" placeholder="KamaludinZ/web_madrasah_2026"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm font-mono">
            </div>
            <div>
                <label for="update-github-token" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    GitHub Token
                    @if($hasToken)<span class="text-emerald-600 dark:text-emerald-400 font-normal">(tersimpan — kosongkan jika tidak ingin mengubah)</span>@endif
                </label>
                <input id="update-github-token" type="password" wire:model="updateGithubTokenInput" placeholder="{{ $hasToken ? '••••••••••••••••' : 'ghp_xxxxxxxxxxxx' }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm font-mono" autocomplete="off">
            </div>
        </div>
        <div class="mt-4">
            <x-filament::button wire:click="saveGithubSettings" color="gray" size="sm">Simpan Konfigurasi</x-filament::button>
        </div>
    </div>

    {{-- Cek & Terapkan Update --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Cek Update</h3>
            <x-filament::button wire:click="checkForUpdate" :disabled="$updateBusy" color="gray" icon="heroicon-o-arrow-path">
                Cek Update Sekarang
            </x-filament::button>
        </div>

        @if($updateBusy)
        <div class="flex items-center gap-2 text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-4 py-3">
            <svg class="w-4 h-4 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Update sedang diterapkan di background. Jangan tutup atau reload halaman ini.
        </div>
        @elseif($updateCheckResult)
            @if($updateCheckResult['available'])
            <div class="rounded-xl border-2 border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-5">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <p class="font-bold text-emerald-800 dark:text-emerald-300">Update tersedia: v{{ $updateCheckResult['current'] }} &rarr; v{{ $updateCheckResult['latest'] }}</p>
                </div>
                @if($updateCheckResult['notes'])
                <div class="prose prose-sm dark:prose-invert max-w-none bg-white dark:bg-gray-900 rounded-lg p-3 mb-4 max-h-48 overflow-y-auto text-xs">
                    {!! \Illuminate\Support\Str::markdown($updateCheckResult['notes']) !!}
                </div>
                @endif
                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 mb-3">
                    <input type="checkbox" wire:model.live="updateConfirm" class="mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span>Saya paham update akan mengubah kode aplikasi. Backup otomatis dibuat lebih dulu, dan bila server tidak mendukung eksekusi perintah otomatis, langkah <code class="font-mono text-xs">composer install</code> &amp; <code class="font-mono text-xs">migrate</code> perlu dijalankan manual via SSH.</span>
                </label>
                <x-filament::button color="danger" wire:click="applyUpdate" :disabled="! $updateConfirm">
                    Terapkan Update Sekarang
                </x-filament::button>
            </div>
            @else
            <div class="flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg px-4 py-3">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Aplikasi sudah menggunakan versi terbaru (v{{ $updateCheckResult['current'] }}).
            </div>
            @endif
        @else
        <p class="text-sm text-gray-400">Klik "Cek Update Sekarang" untuk memeriksa rilis terbaru di GitHub.</p>
        @endif
    </div>

    {{-- ── Update Manual (Upload ZIP) ── --}}
    <div class="rounded-xl border-2 border-amber-200 dark:border-amber-900/50 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-bold text-amber-700 dark:text-amber-400 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
            Update Manual (Upload ZIP)
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">
            Alternatif tanpa GitHub — unggah ZIP kode aplikasi langsung (mis. hotfix yang belum sempat dirilis
            sebagai tag). ZIP boleh berisi file project langsung di root, atau dibungkus satu folder. Sistem otomatis
            membuat backup pengaman dan menolak ZIP yang tidak terlihat seperti kode aplikasi Laravel
            (harus ada <code class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">composer.json</code>
            dan <code class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">artisan</code>).
        </p>

        @if($updateBusy)
        <div class="flex items-center gap-2 text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-4 py-3">
            <svg class="w-4 h-4 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Sedang ada proses update berjalan. Tunggu sampai selesai sebelum memulai yang baru.
        </div>
        @else
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[220px]">
                <label for="manual-update-file-input" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">File ZIP Kode Aplikasi</label>
                <input id="manual-update-file-input" type="file" wire:model="manualUpdateFile" accept=".zip"
                       class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-300 hover:file:bg-amber-100 border border-gray-300 dark:border-gray-600 rounded-lg">
                @error('manualUpdateFile') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <div wire:loading wire:target="manualUpdateFile" class="text-xs text-gray-400 mt-1">Mengunggah...</div>
            </div>
            <div class="w-40">
                <label for="manual-update-version" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Versi Baru</label>
                <input id="manual-update-version" type="text" wire:model="manualUpdateVersion" placeholder="mis. 1.1.0"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm font-mono">
            </div>
            <x-filament::button color="warning" wire:click="openManualUpdateConfirm" :disabled="! $manualUpdateFile">
                Lanjutkan Update
            </x-filament::button>
        </div>
        @endif

        {{-- Modal konfirmasi update manual --}}
        @if($showManualUpdateConfirmModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60"
             wire:click.self="cancelManualUpdateConfirm"
             x-data
             x-trap.inert.noscroll="true"
             x-on:keydown.escape.window="$wire.cancelManualUpdateConfirm()">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6"
                 role="dialog" aria-modal="true" aria-labelledby="manual-update-confirm-title">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 id="manual-update-confirm-title" class="text-lg font-bold text-gray-900 dark:text-white">Konfirmasi Update Manual</h3>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Kode aplikasi akan ditimpa dengan isi
                    <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ $manualUpdateFile?->getClientOriginalName() }}</span>,
                    versi tercatat sebagai <strong class="font-mono">v{{ $manualUpdateVersion }}</strong>.
                    Data (database, file media, <code class="font-mono">.env</code>) tidak disentuh.
                </p>
                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 mb-4">
                    <input type="checkbox" wire:model.live="manualUpdateConfirm" class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    <span>Saya paham update akan mengubah kode aplikasi dan sudah menyiapkan ZIP yang benar.</span>
                </label>
                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="cancelManualUpdateConfirm">Batal</x-filament::button>
                    <x-filament::button color="warning" wire:click="runManualUpdate" :disabled="! $manualUpdateConfirm">
                        Ya, Terapkan Update Sekarang
                    </x-filament::button>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Riwayat Update --}}
    @if($updateHistory->isNotEmpty())
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Riwayat Update</h3>
        <div class="space-y-3">
            @foreach($updateHistory as $u)
            <div class="border border-gray-100 dark:border-gray-800 rounded-lg p-3" wire:key="update-{{ $u->id }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-mono text-gray-700 dark:text-gray-300">v{{ $u->from_version }} &rarr; v{{ $u->to_version }}</p>
                    <span @class([
                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                        'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' => $u->status === 'running',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' => $u->status === 'completed',
                        'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $u->status === 'failed',
                    ])>
                        {{ match($u->status) { 'running' => 'Berjalan', 'completed' => 'Selesai', 'failed' => 'Gagal', default => $u->status } }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $u->created_at->format('d M Y H:i') }}</p>
                @if($u->error)
                <p class="text-xs text-red-500 mt-1">{{ $u->error }}</p>
                @endif
                @if($u->log)
                <details class="mt-2">
                    <summary class="text-xs text-primary-600 dark:text-primary-400 cursor-pointer hover:underline">Lihat log</summary>
                    <pre class="mt-1 text-xs bg-gray-950 text-gray-300 rounded p-2 overflow-x-auto whitespace-pre-wrap max-h-64 overflow-y-auto">{{ $u->log }}</pre>
                </details>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

</x-filament-panels::page>
