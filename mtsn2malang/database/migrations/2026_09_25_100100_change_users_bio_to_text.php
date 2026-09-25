<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Form profil & pengguna mengizinkan bio hingga 500 karakter, tetapi kolom
 * lama varchar(255) membuat penyimpanan bio yang lebih panjang gagal (error 500).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio')->nullable()->change();
        });
    }
};
