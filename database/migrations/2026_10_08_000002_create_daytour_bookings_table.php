<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daytour_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('guest_name');
            $table->string('contact_number');
            $table->integer('pax')->default(1);
            $table->decimal('entrance_fee', 10, 2)->default(100.00);
            $table->decimal('extra_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->json('inclusions'); // drink choices + suman per person
            $table->json('extra_orders')->nullable(); // paid add-ons
            $table->string('payment_method');
            $table->string('manual_receipt_no');
            $table->timestamp('visit_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daytour_bookings');
    }
};
