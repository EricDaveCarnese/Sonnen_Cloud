<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'movement_type',
        'quantity_change',
        'quantity_after',
        'reference',
        'notes',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:3',
        'quantity_after'  => 'decimal:3',
    ];

    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
    public function user()          { return $this->belongsTo(User::class); }
}