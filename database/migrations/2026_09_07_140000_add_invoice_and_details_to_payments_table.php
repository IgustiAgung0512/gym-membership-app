<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('invoice_number', 50)->nullable()->unique()->after('id');
            $table->string('payment_method', 30)->default('qris')->after('amount');
            $table->string('type', 30)->default('renewal')->after('payment_method');
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'payment_method', 'type', 'notes']);
        });
    }
};
