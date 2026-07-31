<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('news_id');
        });

        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_post_id')->constrained('gallery_posts')->onDelete('cascade');
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index('gallery_post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('gallery_posts');
    }
};
