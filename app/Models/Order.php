<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'daytour_booking_id',
        'order_date',
        'total_price',
        'order_status',
    ];

    public function user()            { return $this->belongsTo(User::class); }
    public function booking()         { return $this->belongsTo(Booking::class); }
    public function daytourBooking()  { return $this->belongsTo(DaytourBooking::class, 'daytour_booking_id'); }
    public function orderItems()      { return $this->hasMany(OrderItem::class); }
    public function payments()        { return $this->hasMany(Payment::class); }
}