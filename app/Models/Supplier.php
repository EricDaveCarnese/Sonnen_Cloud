<?php

namespace App\Models;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['supplier_name', 
                            'contact_person', 
                            'phone_number', 
                            'email', 
                            'address'];

    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}
