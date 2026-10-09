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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->foreignId('user_id')->constrained('users');
            $table->date('order_date');
            $table->decimal('quantity_ordered', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->enum('purchase_order_status', ['pending', 'received'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
