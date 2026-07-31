<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['slider', 'text'])->default('text');
            $table->boolean('is_active')->default(true);
            $table->boolean('show_once')->default(true)->comment('Tampil sekali per sesi browser');
            $table->date('start_date')->nullable()->comment('Mulai tampil (kosong = langsung aktif)');
            $table->date('end_date')->nullable()->comment('Berhenti tampil (kosong = tidak ada batas)');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('popup_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['image', 'text'])->default('image');
            $table->string('image')->nullable()->comment('Path gambar di storage');
            $table->string('image_link')->nullable()->comment('URL tujuan klik gambar');
            $table->longText('content')->nullable()->comment('Konten rich text');
            $table->string('caption')->nullable()->comment('Keterangan gambar');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_items');
        Schema::dropIfExists('popups');
    }
};
