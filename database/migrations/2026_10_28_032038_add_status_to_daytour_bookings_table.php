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
    Schema::table('daytour_bookings', function (Blueprint $table) {
        // 'pending' = paid at gate, waiting at canteen
        // 'served' = drinks handed over
        $table->enum('status', ['pending', 'served'])->default('served')->after('payment_method');
        
        // We need to make the inclusions JSON nullable because they don't pick drinks at the gate
        $table->json('inclusions')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('daytour_bookings', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
};
