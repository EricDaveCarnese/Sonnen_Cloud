<?php

namespace App\Models;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 
                            'menu_item_id', 
                            'quantity', 
                            'unit_price', 
                            'subtotal'];

    public function order() { return $this->belongsTo(Order::class); }
    public function menuItem() { return $this->belongsTo(MenuItem::class); }
}
