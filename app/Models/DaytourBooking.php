<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaytourBooking extends Model
{
    protected $fillable = [
        'user_id',
        'guest_name',
        'contact_number',
        'pax',
        'entrance_fee',
        'extra_amount',
        'total_amount',
        'inclusions',
        'extra_orders',
        'payment_method',
        'manual_receipt_no',
        'visit_date',
    ];

    protected $casts = [
        'inclusions'   => 'array',
        'extra_orders' => 'array',
        'entrance_fee' => 'decimal:2',
        'extra_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'visit_date'   => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}