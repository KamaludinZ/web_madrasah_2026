<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saran: pisahkan tanggapan yang boleh dibaca pengirim (public_response,
     * tampil di halaman lacak) dari catatan internal petugas (response).
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->text('public_response')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('public_response');
        });
    }
};
