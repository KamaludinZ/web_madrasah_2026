<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_coordinators', function (Blueprint $table) {
            $table->id();
            $table->string('angkatan');
            $table->string('nama_koordinator');
            $table->string('kontak')->nullable();
            $table->string('foto')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_coordinators');
    }
};
