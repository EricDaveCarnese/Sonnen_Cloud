<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakfastOrder extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'pax',
        'order_details',
        'included_amount',
        'extra_amount',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'order_details'   => 'array',
        'included_amount' => 'decimal:2',
        'extra_amount'    => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
    public function user()    { return $this->belongsTo(User::class); }
}