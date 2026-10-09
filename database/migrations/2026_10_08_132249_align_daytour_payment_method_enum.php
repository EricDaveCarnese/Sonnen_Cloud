<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize any existing free-text values first
        DB::table('daytour_bookings')->whereNotIn('payment_method', ['cash', 'gcash', 'bank_transfer'])
            ->update(['payment_method' => 'cash']);

        Schema::table('daytour_bookings', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'gcash', 'bank_transfer'])
                ->default('cash')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('daytour_bookings', function (Blueprint $table) {
            $table->string('payment_method')->change();
        });
    }
};