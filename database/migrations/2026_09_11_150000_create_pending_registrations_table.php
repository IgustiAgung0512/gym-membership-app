<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('membership_package_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('gender', 5)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('password'); // Hashed password
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('qris');
            $table->enum('status', ['pending', 'paid', 'cancelled', 'expired'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
