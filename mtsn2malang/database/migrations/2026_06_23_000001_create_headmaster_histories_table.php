<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('headmaster_histories', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Nama lengkap
            $table->string('nip')->nullable(); // NIP
            $table->string('period');          // Periode jabatan, misal "2018 - 2022"
            $table->string('photo')->nullable(); // Path foto
            $table->integer('order')->default(0); // Urutan tampil
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('headmaster_histories');
    }
};
