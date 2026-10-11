<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\DaytourBooking;
use App\Models\Guest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'guest_id',
        'order_id',
        'daytour_booking_id',
        'user_id',
        'amount_paid',
        'payment_type',
        'payment_method',
        'payment_date',
        'manual_receipt_no',
    ];

    public function booking()          { return $this->belongsTo(Booking::class); }
    public function guest()            { return $this->belongsTo(Guest::class); }
    public function order()            { return $this->belongsTo(Order::class); }
    public function daytourBooking()   { return $this->belongsTo(DaytourBooking::class, 'daytour_booking_id'); }
    public function user()             { return $this->belongsTo(User::class); }
}