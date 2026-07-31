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
        Schema::table('social_media_contents', function (Blueprint $table) {
            $table->boolean('show_on_madrasah')->default(true)->after('is_active');
            $table->boolean('show_on_mahad')->default(true)->after('show_on_madrasah');

            $table->index(['is_active', 'show_on_madrasah']);
            $table->index(['is_active', 'show_on_mahad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_contents', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'show_on_madrasah']);
            $table->dropIndex(['is_active', 'show_on_mahad']);
            $table->dropColumn(['show_on_madrasah', 'show_on_mahad']);
        });
    }
};
