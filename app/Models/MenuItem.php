<?php

namespace App\Models;

use App\Models\MenuItemIngredient;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'item_name',
        'category',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function ingredients() { return $this->hasMany(MenuItemIngredient::class); }
    public function orderItems()  { return $this->hasMany(OrderItem::class); }

    /**
     * Compute the maximum number of servings that can be sold
     * given current inventory levels.
     *
     * - If the item has a recipe: min over ingredients of floor(stock / qty_per_serving)
     * - If the item has no recipe: look for InventoryItem with the same name (direct-resale)
     * - If neither exists: return null (unlimited — item doesn't consume inventory)
     *
     * Requires `ingredients.inventoryItem` to be eager-loaded.
     */
    public function getAvailableServingsAttribute(): ?int
    {
        $this->loadMissing('ingredients.inventoryItem');

        // Recipe-based item
        if ($this->ingredients->isNotEmpty()) {
            $min = PHP_INT_MAX;

            foreach ($this->ingredients as $ing) {
                if (! $ing->inventoryItem) {
                    return 0; // broken recipe — treat as unavailable
                }

                $perServing = (float) $ing->quantity_per_serving;
                if ($perServing <= 0) {
                    continue; // nothing to deduct, skip
                }

                $available = (float) $ing->inventoryItem->quantity_on_hand;
                $servings  = (int) floor($available / $perServing);
                $min       = min($min, $servings);
            }

            return $min === PHP_INT_MAX ? null : $min;
        }

        // Direct-resale fallback — match inventory by name
        $stock = InventoryItem::whereRaw(
            'LOWER(TRIM(item_name)) = ?',
            [strtolower(trim($this->item_name))]
        )->first();

        if ($stock) {
            return (int) floor((float) $stock->quantity_on_hand);
        }

        return null; // unlimited — no inventory impact
    }

    /**
     * Convenience boolean for UI.
     */
    public function getIsAvailableAttribute(): bool
    {
        $available = $this->available_servings;
        return $available === null || $available > 0;
    }
}