<?php

namespace App\Models;

use App\Models\MenuItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'item_name',
        'category',
        'quantity_on_hand',
        'unit_of_measure',
        'reorder_level',
        'preferred_supplier_id',
    ];

    public function menuItems()       { return $this->hasMany(MenuItem::class); }
    public function purchaseOrders()  { return $this->hasMany(PurchaseOrder::class); }
    public function preferredSupplier() { return $this->belongsTo(Supplier::class, 'preferred_supplier_id'); }
}