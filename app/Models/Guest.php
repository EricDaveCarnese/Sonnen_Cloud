<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = ['first_name',
                            'last_name',
                            'contact_number', 
                            'email_address', 
                            'source'];

    public function bookings() { return $this->hasMany(Booking::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
