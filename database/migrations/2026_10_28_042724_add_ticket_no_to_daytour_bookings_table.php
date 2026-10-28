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
        $table->integer('ticket_no')->nullable()->unique()->after('id');
    });
}

public function down(): void
{
    Schema::table('daytour_bookings', function (Blueprint $table) {
        $table->dropColumn('ticket_no');
    });
}
};
