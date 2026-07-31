@php
    $roleName = $user->getRoleNames()->first() ?? null;
    $roleMap  = [
        'super_admin'     => ['label' => 'Super Administrator', 'emoji' => '👑',
                              'bg' => 'bg-red-50 dark:bg-red-900/20',
                              'text' => 'text-red-700 dark:text-red-300',
                              'dot' => 'bg-red-400'],
        'editor_in_chief' => ['label' => 'Editor in Chief',    'emoji' => '✍️',
                              'bg' => 'bg-violet-50 dark:bg-violet-900/20',
                              'text' => 'text-violet-700 dark:text-violet-300',
                              'dot' => 'bg-violet-400'],
        'editor'          => ['label' => 'Editor',             'emoji' => '📝',
                              'bg' => 'bg-blue-50 dark:bg-blue-900/20',
                              'text' => 'text-blue-700 dark:text-blue-300',
                              'dot' => 'bg-blue-400'],
        'contributor'     => ['label' => 'Kontributor',        'emoji' => '🖊️',
                              'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
                              'text' => 'text-emerald-700 dark:text-emerald-300',
                              'dot' => 'bg-emerald-400'],
        'moderator'       => ['label' => 'Moderator',          'emoji' => '🛡️',
                              'bg' => 'bg-amber-50 dark:bg-amber-900/20',
                              'text' => 'text-amber-700 dark:text-amber-300',
                              'dot' => 'bg-amber-400'],
    ];
    $role = $roleMap[$roleName] ?? ['label' => '—', 'emoji' => '👤',
                                    'bg' => 'bg-gray-50 dark:bg-gray-700/30',
                                    'text' => 'text-gray-600 dark:text-gray-300',
                                    'dot' => 'bg-gray-400'];
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800 shadow-sm">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/80">
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Informasi Akun
        </p>
        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
            Hanya bisa diubah oleh administrator
        </p>
    </div>

    {{-- Role highlight --}}
    <div class="px-4 py-3.5 {{ $role['bg'] }} border-b border-gray-100 dark:border-gray-700">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Role</p>
        <div class="flex items-center gap-2">
            <span class="text-base leading-none">{{ $role['emoji'] }}</span>
            <span class="text-sm font-semibold {{ $role['text'] }}">{{ $role['label'] }}</span>
        </div>
    </div>

    {{-- Info rows --}}
    <div class="divide-y divide-gray-100 dark:divide-gray-700/60">

        {{-- Email --}}
        <div class="px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Email</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-medium truncate" title="{{ $user->email }}">
                {{ $user->email }}
            </p>
        </div>

        {{-- Bergabung --}}
        <div class="px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Bergabung Sejak</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">
                {{ $user->created_at?->format('d M Y') ?? '—' }}
            </p>
        </div>

        {{-- Status --}}
        <div class="px-4 py-3">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Status</p>
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
            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Username</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">
                @{{ $user->username }}
            </p>
        </div>
        @endif

    </div>
</div>
