<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TAB NAVIGATION UTAMA (murni Livewire, tanpa Alpine entangle)
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="border-b border-gray-200 dark:border-gray-700 mb-6">
    <nav class="flex gap-1 -mb-px overflow-x-auto" aria-label="Main Tabs">
        @foreach([
            ['key' => 'settings', 'label' => 'Pengaturan'],
            ['key' => 'log',      'label' => 'Monitoring Log'],
            ['key' => 'audit',    'label' => 'Audit Aktivitas'],
        ] as $tab)
        <button
            type="button"
            wire:click="$set('activeMainTab', '{{ $tab['key'] }}')"
            @class([
                'px-5 py-3 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap',
                'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
                    => $activeMainTab === $tab['key'],
                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300'
                    => $activeMainTab !== $tab['key'],
            ])
        >
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

</x-filament-panels::page>
