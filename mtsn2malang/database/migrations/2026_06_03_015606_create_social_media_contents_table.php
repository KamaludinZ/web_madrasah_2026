<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_media_contents', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['youtube', 'instagram', 'tiktok']);
            $table->string('title');
            $table->string('post_url');
            $table->string('embed_url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['platform', 'is_active']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_contents');
    }
};
