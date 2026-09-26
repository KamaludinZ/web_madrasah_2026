<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menandai berita yang newsletter-nya sudah dikirim, agar pelanggan tidak
 * menerima email ganda untuk berita yang sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->timestamp('newsletter_sent_at')->nullable()->after('published_at');
        });

        // Berita yang sudah terbit sebelum fitur ini dianggap sudah dikirim —
        // mengedit berita lama tidak boleh memicu email ke semua pelanggan.
        DB::table('news')
            ->where('status', 'published')
            ->update(['newsletter_sent_at' => DB::raw('COALESCE(published_at, NOW())')]);
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('newsletter_sent_at');
        });
    }
};
