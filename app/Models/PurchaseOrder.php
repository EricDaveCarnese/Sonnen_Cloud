<?php

namespace App\Models;

use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['supplier_id', 
                            'inventory_item_id', 
                            'user_id', 
                            'order_date', 
                            'quantity_ordered', 
                            'total_cost', 
                            'purchase_order_status'];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
    public function user() { return $this->belongsTo(User::class); }
}
