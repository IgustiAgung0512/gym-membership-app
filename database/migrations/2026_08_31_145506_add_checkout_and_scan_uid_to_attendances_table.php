<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            $table->foreignId('scan_uid_id')
                ->nullable()
                ->after('rfid_card_id')
                ->constrained('scan_uids')
                ->nullOnDelete();

            $table->dateTime('check_out_at')
                ->nullable()
                ->after('check_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            $table->dropForeign([
                'scan_uid_id'
            ]);

            $table->dropColumn([
                'scan_uid_id',
                'check_out_at',
            ]);
        });
    }
};