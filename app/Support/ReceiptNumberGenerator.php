<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\DaytourBooking;
use Carbon\Carbon;

class ReceiptNumberGenerator
{
    /**
     * Generate the next receipt number in the format OR-YYYYMMDD-NNNN.
     * Uniqueness is global across payments and daytour_bookings tables.
     */
    public static function next(): string
    {
        $datePart = Carbon::now()->format('Ymd');
        $prefix   = "OR-{$datePart}-";

        // Find the highest sequence for today across both payment tables
        $lastPayment  = Payment::where('manual_receipt_no', 'like', $prefix . '%')
            ->orderBy('manual_receipt_no', 'desc')
            ->value('manual_receipt_no');

        $lastDaytour = DaytourBooking::where('manual_receipt_no', 'like', $prefix . '%')
            ->orderBy('manual_receipt_no', 'desc')
            ->value('manual_receipt_no');

        $lastSeq = 0;
        if ($lastPayment) {
            $lastSeq = max($lastSeq, (int) substr($lastPayment, -4));
        }
        if ($lastDaytour) {
            $lastSeq = max($lastSeq, (int) substr($lastDaytour, -4));
        }

        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }
}