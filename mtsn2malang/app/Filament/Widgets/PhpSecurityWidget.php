<?php

namespace App\Filament\Widgets;

use App\Support\PhpSecurity;
use Filament\Widgets\Widget;

/**
 * Peringatan di dashboard (khusus super admin) bila server masih memakai
 * versi PHP yang rentan atau session.upload_progress masih aktif.
 * Tidak tampil sama sekali bila server sudah aman.
 */
class PhpSecurityWidget extends Widget
{
    protected static string $view = 'filament.widgets.php-security-widget';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (auth()->user()?->hasRole('super_admin') ?? false)
            && (PhpSecurity::isVulnerable() || PhpSecurity::uploadProgressEnabled());
    }

    protected function getViewData(): array
    {
        return [
            'version'        => PHP_VERSION,
            'vulnerable'     => PhpSecurity::isVulnerable(),
            'recommendation' => PhpSecurity::recommendation(),
            'uploadProgress' => PhpSecurity::uploadProgressEnabled(),
            'advisory'       => PhpSecurity::ADVISORY,
        ];
    }
}
