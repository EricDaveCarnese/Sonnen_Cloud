<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'guest_id',
        'user_id',
        'check_in_date',
        'check_out_date',
        'total_amount',
        'required_downpayment',
        'booking_status',
        'booking_type',
        'pax',
    ];

    protected $casts = [
        'check_in_date'        => 'datetime',
        'check_out_date'       => 'datetime',
        'total_amount'         => 'decimal:2',
        'required_downpayment' => 'decimal:2',
    ];

    public function guest()          { return $this->belongsTo(Guest::class); }
    public function user()           { return $this->belongsTo(User::class); }
    public function payments()       { return $this->hasMany(Payment::class); }
    public function orders()         { return $this->hasMany(Order::class); }
    public function breakfastOrder() { return $this->hasOne(BreakfastOrder::class); }

    public function getTotalPaidAttribute(): float
    {
        if (array_key_exists('payments_sum_amount_paid', $this->attributes)) {
            return (float) $this->attributes['payments_sum_amount_paid'];
        }
        return (float) $this->payments()->sum('amount_paid');
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->total_paid);
    }

    public function getIsOverdueForFinalPaymentAttribute(): bool
    {
        if ($this->booking_status === 'completed') {
            return false;
        }
        return Carbon::parse($this->created_at)->diffInDays(now()) > 30
            && $this->remaining_balance > 0;
    }

    public function getCanSelectBreakfastAttribute(): bool
    {
        return $this->booking_status === 'confirmed'
            && is_null($this->breakfastOrder)
            && $this->remaining_balance > 0;
    }

    public function recalculateStatus(): void
    {
        $paid  = $this->total_paid;
        $total = (float) $this->total_amount;
        $down  = (float) $this->required_downpayment;

        if ($paid >= $total) {
            $newStatus = 'completed';
        } elseif ($paid >= $down) {
            $newStatus = 'confirmed';
        } else {
            $newStatus = 'pending';
        }

        if ($this->booking_status !== 'cancelled' && $this->booking_status !== $newStatus) {
            $this->update(['booking_status' => $newStatus]);
        }
    }
}