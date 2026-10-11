<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('daytour_booking_id')
                ->nullable()
                ->after('order_id')
                ->constrained('daytour_bookings')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['daytour_booking_id']);
            $table->dropColumn('daytour_booking_id');
        });
    }
};