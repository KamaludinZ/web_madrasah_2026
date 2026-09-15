@php
    $roleName = $user->getRoleNames()->first() ?? null;
    $roleBadge = \App\Support\RoleDisplay::badgeClasses($roleName);
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800 shadow-sm">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/80">
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Informasi Akun
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
            Hanya bisa diubah oleh administrator
        </p>
    </div>

    {{-- Role highlight --}}
    <div class="px-4 py-3.5 {{ $roleBadge['bg'] }} border-b border-gray-100 dark:border-gray-700">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Role</p>
        <div class="flex items-center gap-2">
            <span class="text-base leading-none">{{ \App\Support\RoleDisplay::emoji($roleName) }}</span>
            <span class="text-sm font-semibold {{ $roleBadge['text'] }}">{{ \App\Support\RoleDisplay::label($roleName) }}</span>
        </div>
    </div>

    {{-- Info rows --}}
    <div class="divide-y divide-gray-100 dark:divide-gray-700/60">

        {{-- Email --}}
        <div class="px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Email</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-medium truncate" title="{{ $user->email }}">
                {{ $user->email }}
            </p>
        </div>

        {{-- Bergabung --}}
        <div class="px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Bergabung Sejak</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">
                {{ $user->created_at?->format('d M Y') ?? '—' }}
            </p>
        </div>

        {{-- Status --}}
        <div class="px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Status</p>
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Aktif</span>
            </div>
        </div>

        {{-- Username --}}
        @if($user->username ?? null)
        <div class="px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Username</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">
                @{{ $user->username }}
            </p>
        </div>
        @endif

    </div>
</div>
