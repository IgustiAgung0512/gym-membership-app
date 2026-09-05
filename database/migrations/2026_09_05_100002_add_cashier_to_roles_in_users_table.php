<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah tipe kolom role agar mendukung 'cashier'
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'member'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'member') NOT NULL DEFAULT 'member'");
    }
};
