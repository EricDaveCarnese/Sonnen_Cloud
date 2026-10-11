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

    public function ingredients() { return $this->hasMany(MenuItemIngredient::class); }
    public function orderItems()  { return $this->hasMany(OrderItem::class); }
}