<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: drop constraint lama dan buat ulang dengan tambahan 'saran'
        DB::statement("ALTER TABLE complaints DROP CONSTRAINT IF EXISTS complaints_type_check");
        DB::statement("ALTER TABLE complaints ADD CONSTRAINT complaints_type_check CHECK (type IN ('whistleblowing', 'masyarakat', 'saran'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE complaints DROP CONSTRAINT IF EXISTS complaints_type_check");
        DB::statement("ALTER TABLE complaints ADD CONSTRAINT complaints_type_check CHECK (type IN ('whistleblowing', 'masyarakat'))");
    }
};
