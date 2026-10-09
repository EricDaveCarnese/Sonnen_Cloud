<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_type', ['downpayment', 'full_payment', 'balance']);
            $table->enum('payment_method', ['cash', 'gcash', 'bank_transfer']);
            $table->timestamp('payment_date')->useCurrent();
            $table->string('manual_receipt_no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
