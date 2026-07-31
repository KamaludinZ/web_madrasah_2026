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
        Schema::create('wordpress_migration_batches', function (Blueprint $table) {
            $table->id();
            $table->string('mode')->default('online');
            $table->string('status')->default('completed');
            $table->string('source_file')->nullable();
            $table->string('source_database')->nullable();
            $table->string('source_prefix')->nullable();
            $table->unsignedInteger('migrated_posts')->default(0);
            $table->unsignedInteger('handled_images')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wordpress_migration_batches');
    }
};
