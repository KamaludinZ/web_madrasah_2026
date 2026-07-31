<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_leaders', function (Blueprint $table) {
            $table->id();
            $table->string('photo')->nullable();       // Foto (opsional)
            $table->string('name');                    // Nama lengkap
            $table->string('position');                // Jabatan
            $table->string('nip')->nullable();         // NIP (opsional)
            $table->string('period')->nullable();      // Periode jabatan
            $table->integer('order')->default(0);      // Urutan tampil
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_leaders');
    }
};
