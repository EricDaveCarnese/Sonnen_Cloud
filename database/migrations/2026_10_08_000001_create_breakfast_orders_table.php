<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breakfast_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('pax')->default(1); // number of persons getting breakfast
            $table->json('order_details'); // JSON: set meals per person, selected items, add-ons
            $table->decimal('included_amount', 10, 2)->default(0.00); // free items value
            $table->decimal('extra_amount', 10, 2)->default(0.00); // paid add-ons total
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('status', ['pending', 'confirmed', 'served'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breakfast_orders');
    }
};
