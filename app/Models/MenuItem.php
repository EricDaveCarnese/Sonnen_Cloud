<?php

namespace App\Models;

use App\Models\InventoryItem;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['item_name',
                            'category', 
                            'unit_price', 
                            'inventory_item_id', 
                            'ingredient_qty_per_order'];

    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
}
