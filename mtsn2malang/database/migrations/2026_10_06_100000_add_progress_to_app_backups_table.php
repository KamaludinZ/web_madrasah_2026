<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap backup yang sedang berjalan (mis. "Mengemas file media 120/450"),
 * ditampilkan di tab Backup & Restore. Setiap pembaruan juga menyentuh
 * updated_at sehingga backup yang macet (worker mati) bisa dikenali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_backups', function (Blueprint $table) {
            $table->string('progress')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('app_backups', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
    }
};
