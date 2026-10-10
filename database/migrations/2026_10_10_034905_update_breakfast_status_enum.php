<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert any existing 'confirmed' rows to 'pending'
        DB::table('breakfast_orders')->where('status', 'confirmed')->update(['status' => 'pending']);

        Schema::table('breakfast_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'preparing', 'served'])
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('breakfast_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'served'])
                ->default('pending')
                ->change();
        });
    }
};