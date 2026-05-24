<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Generic Archive Table (for PPID, SAKIP, Pengaduan, Survei, etc.)
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // berkala, setiap_saat, serta_merta, renstra, perkin, lkj, laporan_pengaduan, hasil_survei
            $table->string('title');
            $table->string('cover')->nullable();
            $table->string('category')->nullable();
            $table->date('published_date');
            $table->string('file_path')->nullable(); // uploaded file
            $table->string('external_link')->nullable(); // external link
            $table->enum('link_type', ['file', 'link'])->default('file');
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('published_date');
        });

        // Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, image, boolean, json
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // Static Pages Content
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique(); // sambutan, selayang_pandang, visi_misi, etc.
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->json('extra_data')->nullable(); // for additional structured data
            $table->timestamps();
        });

        // PPDB / PSB
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ppdb', 'psb'])->default('ppdb');
            $table->json('schedule')->nullable(); // jadwal pendaftaran
            $table->text('general_requirements')->nullable();
            $table->text('specific_requirements')->nullable();
            $table->string('brochure_file')->nullable();
            $table->string('brochure_link')->nullable();
            $table->string('registration_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pengaduan (Complaints)
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->enum('type', ['whistleblowing', 'masyarakat'])->default('masyarakat');
            $table->string('name')->nullable(); // bisa anonim
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('content');
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'process', 'resolved', 'rejected'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index('ticket_number');
            $table->index('status');
        });

        // Survey Responses
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['skm', 'spk'])->default('skm');
            $table->string('respondent_name')->nullable();
            $table->string('respondent_type')->nullable(); // siswa, orang tua, masyarakat
            $table->json('answers'); // survey answers
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // Programs (Unggulan, Ma'had)
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['unggulan', 'mahad'])->default('unggulan');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('cover')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('page_contents');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('archives');
    }
};
