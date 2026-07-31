<?php
/**
 * ARTISAN RUNNER — Hanya untuk digunakan sementara saat deploy.
 * HAPUS FILE INI SEGERA setelah selesai digunakan!
 *
 * Akses: https://mtsn2kotamalang.sch.id/artisan-run.php?token=MTSN2MLG2026&cmd=clear-all
 */

define('SECRET_TOKEN', 'MTSN2MLG2026');

if (empty($_GET['token']) || $_GET['token'] !== SECRET_TOKEN) {
    http_response_code(403);
    die('403 Forbidden');
}

$cmd = $_GET['cmd'] ?? 'status';

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/plain; charset=utf-8');
echo "=== ARTISAN RUNNER ===\n";
echo "Waktu  : " . date('Y-m-d H:i:s') . "\n";
echo "Perintah: {$cmd}\n";
echo str_repeat('-', 40) . "\n\n";

$commands = [];

switch ($cmd) {
    case 'clear-all':
        $commands = [
            'view:clear',
            'cache:clear',
            'config:clear',
            'route:clear',
            'event:clear',
        ];
        break;

    case 'migrate':
        $commands = ['migrate --force'];
        break;

    case 'optimize':
        $commands = [
            'view:clear',
            'cache:clear',
            'config:clear',
            'route:clear',
            'config:cache',
            'route:cache',
            'view:cache',
        ];
        break;

    case 'storage-link':
        $commands = ['storage:link'];
        break;

    case 'status':
        $commands = ['about'];
        break;

    default:
        echo "Perintah tidak dikenal: {$cmd}\n";
        echo "\nPerintah tersedia:\n";
        echo "  ?token=MTSN2MLG2026&cmd=clear-all     → Bersihkan semua cache\n";
        echo "  ?token=MTSN2MLG2026&cmd=migrate        → Jalankan migrasi\n";
        echo "  ?token=MTSN2MLG2026&cmd=optimize       → Clear + cache ulang\n";
        echo "  ?token=MTSN2MLG2026&cmd=storage-link   → Buat symlink storage\n";
        echo "  ?token=MTSN2MLG2026&cmd=status         → Info aplikasi\n";
        exit;
}

foreach ($commands as $artisanCmd) {
    echo "▶ php artisan {$artisanCmd}\n";
    $exitCode = $kernel->call($artisanCmd);
    $output = $kernel->output();
    echo $output ?: "(tidak ada output)\n";
    echo "  Exit code: {$exitCode}\n\n";
}

echo str_repeat('-', 40) . "\n";
echo "✅ Selesai.\n";
echo "\n⚠️  HAPUS FILE INI SEKARANG: public/artisan-run.php\n";
