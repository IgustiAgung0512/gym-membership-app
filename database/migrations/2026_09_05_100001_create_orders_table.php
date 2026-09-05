<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('customer_name')->default('Tamu / Non-Member');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('cost_total', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'other'])->default('cash');
            $table->decimal('cash_received', 12, 2)->nullable();
            $table->decimal('cash_change', 12, 2)->nullable();
            $table->enum('payment_status', ['paid', 'pending', 'cancelled'])->default('paid');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
