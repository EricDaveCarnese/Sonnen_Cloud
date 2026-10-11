<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\MenuItem;
use Exception;
use Illuminate\Support\Facades\Auth;

class InventoryDeductor
{
    /**
     * Deduct inventory for a menu item, using recipe or direct-resale fallback.
     *
     * @param  MenuItem    $menuItem   The menu item being sold
     * @param  int         $qty        Number of servings sold
     * @param  string      $reference  Reference code for the movement log (e.g., "ORD-0001", "BK-0004")
     * @param  string|null $notes      Optional notes for the movement log
     * @return array       List of ['item' => InventoryItem, 'quantity' => float]
     * @throws Exception   When stock is insufficient
     */
    public static function deductForMenuItem(MenuItem $menuItem, int $qty, string $reference, ?string $notes = null): array
    {
        $plan = self::buildDeductionPlan($menuItem, $qty);

        if (empty($plan)) {
            // No recipe and no matching inventory - nothing to deduct
            return [];
        }

        // Check all first - atomic
        foreach ($plan as $entry) {
            $stock = $entry['stock'];
            $need  = $entry['quantity'];

            if ($stock->quantity_on_hand < $need) {
                throw new Exception(
                    "Insufficient stock for {$stock->item_name} " .
                    "(needed for {$menuItem->item_name}). " .
                    "Available: {$stock->quantity_on_hand} {$stock->unit_of_measure}, " .
                    "Required: {$need} {$stock->unit_of_measure}."
                );
            }
        }

        // Deduct and log
        $results = [];
        foreach ($plan as $entry) {
            $stock = $entry['stock'];
            $need  = $entry['quantity'];

            $stock->decrement('quantity_on_hand', $need);
            $stock->refresh();

            InventoryMovement::create([
                'inventory_item_id' => $stock->id,
                'user_id'           => Auth::id(),
                'movement_type'     => 'order_deduction',
                'quantity_change'   => -1 * $need,
                'quantity_after'    => $stock->quantity_on_hand,
                'reference'         => $reference,
                'notes'             => $notes,
            ]);

            $results[] = ['item' => $stock, 'quantity' => $need];
        }

        return $results;
    }

    /**
     * Build the deduction plan without executing it.
     * Uses recipe if present, otherwise falls back to name-based 1:1 match.
     */
    private static function buildDeductionPlan(MenuItem $menuItem, int $qty): array
    {
        $menuItem->loadMissing('ingredients.inventoryItem');

        $plan = [];

        if ($menuItem->ingredients->isNotEmpty()) {
            foreach ($menuItem->ingredients as $ing) {
                if (! $ing->inventoryItem) {
                    throw new Exception("Recipe ingredient missing for {$menuItem->item_name}.");
                }
                $plan[] = [
                    'stock'    => $ing->inventoryItem,
                    'quantity' => (float) $ing->quantity_per_serving * $qty,
                ];
            }
            return $plan;
        }

        // Direct-resale fallback: match inventory by name (case-insensitive, trimmed)
        $matchingStock = InventoryItem::whereRaw(
            'LOWER(TRIM(item_name)) = ?',
            [strtolower(trim($menuItem->item_name))]
        )->first();

        if ($matchingStock) {
            $plan[] = [
                'stock'    => $matchingStock,
                'quantity' => (float) $qty,
            ];
        }

        return $plan;
    }
}