<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pickup_status', 30)->default('picked_up')->after('payment_status');
            $table->timestamp('picked_up_at')->nullable()->after('pickup_status');
            $table->foreignId('picked_up_by')->nullable()->after('picked_up_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['picked_up_by']);
            $table->dropColumn(['pickup_status', 'picked_up_at', 'picked_up_by']);
        });
    }
};
