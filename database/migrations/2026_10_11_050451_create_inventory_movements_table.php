<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users');
            $table->enum('movement_type', ['order_deduction', 'po_received']);
            $table->decimal('quantity_change', 12, 3); // negative for deductions, positive for additions
            $table->decimal('quantity_after', 12, 3);  // snapshot of stock after the change
            $table->string('reference')->nullable();    // e.g. "ORD-0005" or "PO-0003"
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};