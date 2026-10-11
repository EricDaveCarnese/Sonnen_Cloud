<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')
                ->constrained('menu_items')
                ->onDelete('cascade');
            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->onDelete('restrict');
            $table->decimal('quantity_per_serving', 10, 3);
            $table->timestamps();

            // Prevent duplicate ingredient rows per menu item
            $table->unique(['menu_item_id', 'inventory_item_id'], 'mi_ing_unique');
        });

        // Data migration: convert existing inventory_item_id links into recipe entries
        $existing = DB::table('menu_items')
            ->whereNotNull('inventory_item_id')
            ->get(['id', 'inventory_item_id', 'ingredient_qty_per_order']);

        $now = now();
        foreach ($existing as $row) {
            DB::table('menu_item_ingredients')->insert([
                'menu_item_id'         => $row->id,
                'inventory_item_id'    => $row->inventory_item_id,
                'quantity_per_serving' => $row->ingredient_qty_per_order ?? 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        // Drop the old columns from menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->dropColumn(['inventory_item_id', 'ingredient_qty_per_order']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('inventory_item_id')->nullable()->after('unit_price')
                ->constrained('inventory_items')->onDelete('set null');
            $table->decimal('ingredient_qty_per_order', 8, 2)->default(1.00)->after('inventory_item_id');
        });

        // Reverse-migrate: pick the first ingredient per menu item (lossy)
        $recipes = DB::table('menu_item_ingredients')
            ->orderBy('id')
            ->get()
            ->groupBy('menu_item_id');

        foreach ($recipes as $menuItemId => $ingredients) {
            $first = $ingredients->first();
            DB::table('menu_items')->where('id', $menuItemId)->update([
                'inventory_item_id'        => $first->inventory_item_id,
                'ingredient_qty_per_order' => $first->quantity_per_serving,
            ]);
        }

        Schema::dropIfExists('menu_item_ingredients');
    }
};