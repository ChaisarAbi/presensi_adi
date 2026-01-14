<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, we need to modify the enum constraint
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Hadir Masuk', 'Hadir Pulang', 'Izin', 'Tidak Hadir', 'Terlambat') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Hadir Masuk', 'Hadir Pulang', 'Izin', 'Tidak Hadir') NOT NULL");
    }
};
