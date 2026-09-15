<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->nullable();
            $table->string('type')->default('manual'); // manual, pre_restore, pre_update
            $table->string('status')->default('running'); // running, completed, failed
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('app_version')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('app_restores', function (Blueprint $table) {
            $table->id();
            $table->string('source_filename');
            $table->string('status')->default('running'); // running, completed, failed
            $table->string('safety_backup_filename')->nullable();
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
        Schema::dropIfExists('app_restores');
        Schema::dropIfExists('app_backups');
    }
};
