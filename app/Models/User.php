<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'fullname',
        'username',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function bookings() { return $this->hasMany(Booking::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function purchaseOrders() { return $this->hasMany(PurchaseOrder::class); }
}