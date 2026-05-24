<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // GTK (Guru dan Tenaga Kependidikan)
        Schema::create('gtk', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nip')->nullable();
            $table->string('position'); // jabatan
            $table->enum('type', ['guru', 'tendik'])->default('guru'); // guru atau tenaga kependidikan
            $table->string('subject')->nullable(); // mata pelajaran
            $table->string('education')->nullable(); // pendidikan terakhir
            $table->string('photo')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Ekstrakurikuler
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->string('schedule')->nullable();
            $table->string('coach')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Sarpras (Sarana Prasarana)
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('category')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Prestasi
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('competition_name');
            $table->enum('level', ['regional', 'provinsi', 'nasional', 'internasional']);
            $table->string('rank'); // juara 1, 2, 3, harapan, dll
            $table->string('academic_year'); // tahun pelajaran
            $table->date('achievement_date')->nullable();
            $table->string('organizer')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();

            $table->index('level');
            $table->index('academic_year');
        });

        // Testimonials / Alumni
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('graduation_year', 4);
            $table->text('content');
            $table->string('photo')->nullable();
            $table->string('current_school')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Portal Apps
        Schema::create('portal_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('link');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Standar Pelayanan
        Schema::create('service_standards', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nama pelayanan
            $table->json('requirements')->nullable(); // persyaratan
            $table->json('procedures')->nullable(); // prosedur
            $table->string('service_time')->nullable(); // waktu pelayanan
            $table->string('cost')->nullable(); // biaya/tarif
            $table->string('product')->nullable(); // produk layanan
            $table->text('complaint_handling')->nullable(); // pengelolaan pengaduan
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Page Views Counter
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('page')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('session_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->date('viewed_date');
            $table->timestamps();

            $table->index('viewed_date');
            $table->index(['session_id', 'page']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('service_standards');
        Schema::dropIfExists('portal_apps');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('extracurriculars');
        Schema::dropIfExists('gtk');
    }
};
