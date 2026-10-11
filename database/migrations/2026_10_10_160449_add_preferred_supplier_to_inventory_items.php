<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('preferred_supplier_id')
                ->nullable()
                ->after('reorder_level')
                ->constrained('suppliers')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['preferred_supplier_id']);
            $table->dropColumn('preferred_supplier_id');
        });
    }
};