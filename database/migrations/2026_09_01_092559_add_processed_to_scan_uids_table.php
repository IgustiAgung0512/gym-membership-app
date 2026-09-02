<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_uids', function (Blueprint $table) {
            $table->boolean('processed')
                ->default(false)
                ->after('uid');
        });
    }

    public function down(): void
    {
        Schema::table('scan_uids', function (Blueprint $table) {
            $table->dropColumn('processed');
        });
    }
};
