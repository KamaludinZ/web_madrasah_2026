<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel riwayat backup/restore/update tidak ikut dibackup/direstore
 * (BackupService::EXCLUDED_TABLES). Foreign key triggered_by → users membuat
 * restore gagal, karena psql tidak bisa menghapus & membuat ulang tabel users
 * selama masih dirujuk tabel yang tidak ikut direstore. Kolomnya tetap ada
 * (plus index), hanya relasi di level database yang dilepas.
 */
return new class extends Migration
{
    private const TABLES = ['app_backups', 'app_restores', 'app_updates'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropForeign("{$table}_triggered_by_foreign");
                $blueprint->index('triggered_by');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['triggered_by']);
                $blueprint->foreign('triggered_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
};
