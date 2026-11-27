<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Setiap halaman berita mengambil komentar yang disetujui untuk berita itu
 * (news_id + status). Tanpa indeks, PostgreSQL membaca seluruh tabel
 * komentar di setiap kunjungan — makin lambat seiring bertambahnya komentar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('comments', ['news_id', 'status'])) {
            Schema::table('comments', function (Blueprint $table) {
                $table->index(['news_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('comments', ['news_id', 'status'])) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex(['news_id', 'status']);
            });
        }
    }
};
