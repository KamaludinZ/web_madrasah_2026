<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data Halaman Portal (Tab Portal): kategori, link anak per kategori, dan
 * tombol Akses Cepat. Sengaja terpisah dari portal_apps lama yang tetap
 * dipakai etalase "Portal Aplikasi" di Beranda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon', 50)->default('link')->comment('Nama ikon (lihat components/portal-icon)');
            $table->text('description')->nullable();
            $table->string('main_link')->nullable()->comment('Tujuan bila kategori tidak punya link anak');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('portal_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_category_id')->constrained('portal_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('icon', 50)->default('link');
            $table->text('description')->nullable();
            $table->string('url');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['portal_category_id', 'is_active', 'sort_order']);
        });

        Schema::create('portal_quick_access', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon', 50)->default('link');
            $table->string('url');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_quick_access');
        Schema::dropIfExists('portal_links');
        Schema::dropIfExists('portal_categories');
    }
};
