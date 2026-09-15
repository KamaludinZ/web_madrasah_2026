<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_updates', function (Blueprint $table) {
            $table->id();
            $table->string('from_version');
            $table->string('to_version');
            $table->string('status')->default('running'); // running, completed, failed
            $table->string('release_tag')->nullable();
            $table->string('backup_filename')->nullable(); // backup otomatis sebelum update ini
            $table->longText('log')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_updates');
    }
};
