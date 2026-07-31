<?php

namespace App\Filament\Resources\Concerns;

trait HasRoleAccess
{
    /**
     * Cek apakah user yang sedang login memiliki salah satu dari role yang diberikan.
     */
    protected static function currentUserHasRole(array $roles): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        // super_admin selalu punya akses ke semua
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->hasAnyRole($roles);
    }
}
