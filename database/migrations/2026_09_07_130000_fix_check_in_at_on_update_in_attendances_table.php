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
        // Hilangkan ON UPDATE CURRENT_TIMESTAMP pada check_in_at agar tidak otomatis ter-overwrite saat check-out di MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE attendances CHANGE check_in_at check_in_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
