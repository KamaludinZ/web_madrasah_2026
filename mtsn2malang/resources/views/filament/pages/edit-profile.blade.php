<x-filament-panels::page>
@php
    $user     = $this->getUser();
    $roleName = $user->getRoleNames()->first() ?? null;
    $roleMap  = [
        'super_admin'     => ['label' => 'Super Administrator', 'emoji' => '👑',
                              'class' => 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-800'],
        'editor_in_chief' => ['label' => 'Editor in Chief',    'emoji' => '✍️',
                              'class' => 'bg-violet-50 text-violet-700 ring-1 ring-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:ring-violet-800'],
        'editor'          => ['label' => 'Editor',             'emoji' => '📝',
                              'class' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800'],
        'contributor'     => ['label' => 'Kontributor',        'emoji' => '🖊️',
                              'class' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800'],
        'moderator'       => ['label' => 'Moderator',          'emoji' => '🛡️',
                              'class' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800'],
    ];
    $role    = $roleMap[$roleName] ?? ['label' => '—', 'emoji' => '👤', 'class' => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600'];
    $backUrl = filament()->getUrl();
@endphp

{{-- ── Profile Header Card ── --}}
<div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm mb-6">

    {{-- Banner --}}
    <div class="h-28 relative overflow-hidden"
         style="background:linear-gradient(135deg,#14532d 0%,#166534 35%,#15803d 65%,#16a34a 100%)">
        <svg class="absolute inset-0 w-full h-full opacity-[0.07]" xmlns="http://www.w3.org/2000/svg">
            <defs><pattern id="dp" width="20" height="20" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="1.5" fill="white"/>
            </pattern></defs>
            <rect width="100%" height="100%" fill="url(#dp)"/>
        </svg>
        <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-8 right-1/3 w-32 h-32 rounded-full bg-white/5"></div>
    </div>

    {{-- Content --}}
    <div class="bg-white dark:bg-gray-800 px-5 sm:px-8 pb-5">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-10">

            {{-- Avatar --}}
            <div class="relative z-10 flex-shrink-0">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover
                            ring-4 ring-white dark:ring-gray-800 shadow-lg
                            bg-gray-100 dark:bg-gray-700">
                <span class="absolute bottom-1 right-1 w-3.5 h-3.5 rounded-full
                             bg-emerald-400 ring-2 ring-white dark:ring-gray-800"></span>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0 sm:pb-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white truncate">
                            {{ $user->name }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-0.5">
                            {{ $user->email }}
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                                 text-xs font-semibold flex-shrink-0 {{ $role['class'] }}">
                        {{ $role['emoji'] }} {{ $role['label'] }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2.5">
                    @if($user->created_at)
                    <span class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Bergabung {{ $user->created_at->format('d M Y') }}
                    </span>
                    @endif
                    <span class="flex items-center gap-1 text-xs text-emerald-500 dark:text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Akun Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Override: pastikan avatar upload benar-benar center --}}
<style>
    /* Center avatar upload di dalam section */
    .fi-fo-field-wrp:has([data-field="avatar"]) > div,
    .fi-fo-field-wrp:has(.fi-fo-file-upload) > div {
        display: flex;
        justify-content: center;
    }
    /* Hilangkan label kosong avatar */
    .fi-fo-field-wrp:has([data-field="avatar"]) label:empty { display: none; }
</style>

{{-- ── Form Filament Native ── --}}
<x-filament-panels::form id="form" wire:submit="save">
    {{ $this->form }}

    {{-- Actions: Kembali + Simpan dalam satu baris --}}
    <div class="flex items-center justify-between gap-3 mt-2">
        <a href="{{ $backUrl }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                  text-gray-600 dark:text-gray-400
                  bg-white dark:bg-gray-800
                  border border-gray-300 dark:border-gray-600
                  hover:bg-gray-50 dark:hover:bg-gray-700
                  transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="false"
        />
    </div>
</x-filament-panels::form>

</x-filament-panels::page>
