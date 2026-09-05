<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 50)->nullable()->unique();
            $table->string('category', 50)->default('drinks'); // drinks, supplements, snacks, gear, other
            $table->decimal('price', 12, 2); // Selling price
            $table->decimal('cost_price', 12, 2)->default(0); // Buying/HPP price
            $table->integer('stock')->default(0);
            $table->integer('min_stock_alert')->default(5);
            $table->string('unit', 30)->default('pcs'); // pcs, scoop, botol, pack, cup
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
