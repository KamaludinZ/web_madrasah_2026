<?php
/**
 * DB Migration Helper — HAPUS SETELAH DIGUNAKAN!
 * Akses: https://mtsn2kotamalang.sch.id/db-migrate.php?token=MTSN2MLG2026
 */
if (empty($_GET['token']) || $_GET['token'] !== 'MTSN2MLG2026') {
    http_response_code(403); die('403 Forbidden');
}

// Load .env manual
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
    [$key, $val] = explode('=', $line, 2);
    $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
}

// Koneksi PostgreSQL langsung
try {
    $dsn = "pgsql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']}";
    $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== DB Migration Helper ===\n";
echo "Waktu: " . date('Y-m-d H:i:s') . "\n\n";

$action = $_GET['action'] ?? 'status';

if ($action === 'mark-ran') {
    // Cek batch tertinggi
    $maxBatch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
    $newBatch = $maxBatch + 1;

    $toMark = [
        '2026_06_23_073306_create_jobs_table',
        '2026_06_23_075351_create_failed_jobs_table',
        '2026_06_23_075617_create_activity_log_table',
        '2026_06_23_075618_add_event_column_to_activity_log_table',
        '2026_06_23_075619_add_batch_uuid_column_to_activity_log_table',
    ];

    $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?) ON CONFLICT (migration) DO NOTHING");
    foreach ($toMark as $migration) {
        $stmt->execute([$migration, $newBatch]);
        $rows = $stmt->rowCount();
        echo ($rows ? "✅ Marked: " : "⏭️  Skip (sudah ada): ") . $migration . "\n";
    }
    echo "\nBatch: {$newBatch}\n";

} elseif ($action === 'run-new') {
    // Jalankan migrasi baru yang benar-benar belum ada tabelnya
    $migrations = [
        'gallery_posts' => "
            CREATE TABLE IF NOT EXISTS gallery_posts (
                id bigserial PRIMARY KEY,
                news_id bigint NOT NULL REFERENCES news(id) ON DELETE CASCADE,
                title varchar(255) NOT NULL,
                description text NULL,
                created_at timestamp NULL,
                updated_at timestamp NULL
            );
            CREATE INDEX IF NOT EXISTS gallery_posts_news_id_index ON gallery_posts (news_id);
            CREATE TABLE IF NOT EXISTS gallery_photos (
                id bigserial PRIMARY KEY,
                gallery_post_id bigint NOT NULL REFERENCES gallery_posts(id) ON DELETE CASCADE,
                file_path varchar(255) NOT NULL,
                caption varchar(255) NULL,
                \"order\" smallint NOT NULL DEFAULT 0,
                created_at timestamp NULL,
                updated_at timestamp NULL
            );
            CREATE INDEX IF NOT EXISTS gallery_photos_gallery_post_id_index ON gallery_photos (gallery_post_id);
        ",
        'badge_achievements' => "
            CREATE TABLE IF NOT EXISTS badge_achievements (
                id bigserial PRIMARY KEY,
                name varchar(255) NOT NULL,
                logo varchar(255) NULL,
                \"order\" smallint NOT NULL DEFAULT 0,
                is_active boolean NOT NULL DEFAULT true,
                created_at timestamp NULL,
                updated_at timestamp NULL
            );
        ",
        'school_leaders' => "
            CREATE TABLE IF NOT EXISTS school_leaders (
                id bigserial PRIMARY KEY,
                name varchar(255) NOT NULL,
                period varchar(255) NULL,
                photo varchar(255) NULL,
                \"order\" smallint NOT NULL DEFAULT 0,
                created_at timestamp NULL,
                updated_at timestamp NULL
            );
        ",
    ];

    foreach ($migrations as $table => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Tabel '{$table}' berhasil dibuat/sudah ada.\n";

            // Tandai di tabel migrations
            $maxBatch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
            $migName = "2026_06_25_000001_create_{$table}_table";
            if ($table === 'badge_achievements') $migName = '2026_06_26_100001_create_badge_achievements_table';
            if ($table === 'school_leaders') $migName = '2026_06_25_200000_create_school_leaders_table';
            $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?) ON CONFLICT (migration) DO NOTHING")
                ->execute([$migName, $maxBatch + 1]);
        } catch (Exception $e) {
            echo "❌ Error '{$table}': " . $e->getMessage() . "\n";
        }
    }

} elseif ($action === 'clear-cache') {
    // Hapus file cache Laravel secara manual
    $dirs = [
        __DIR__ . '/../storage/framework/views/',
        __DIR__ . '/../storage/framework/cache/data/',
        __DIR__ . '/../bootstrap/cache/',
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) { echo "⏭️  Skip (tidak ada): {$dir}\n"; continue; }
        $files = glob($dir . '*');
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file)) { unlink($file); $count++; }
        }
        echo "✅ Hapus {$count} file dari: " . basename(dirname($dir)) . "/" . basename($dir) . "\n";
    }

} else {
    // Status
    $rows = $pdo->query("SELECT migration, batch FROM migrations ORDER BY batch, migration")->fetchAll(PDO::FETCH_ASSOC);
    echo "Migrasi di database:\n";
    foreach ($rows as $row) {
        echo "  [{$row['batch']}] {$row['migration']}\n";
    }
    echo "\nURL tersedia:\n";
    echo "  ?token=MTSN2MLG2026&action=mark-ran    → Tandai migrasi lama sebagai Ran\n";
    echo "  ?token=MTSN2MLG2026&action=run-new     → Buat tabel baru (gallery, badge, school_leaders)\n";
    echo "  ?token=MTSN2MLG2026&action=clear-cache → Hapus cache Laravel\n";
}

echo "\n⚠️  HAPUS FILE INI SEKARANG: public/db-migrate.php\n";
