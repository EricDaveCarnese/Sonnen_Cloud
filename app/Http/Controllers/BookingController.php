<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['guest', 'payments', 'breakfastOrder'])
            ->withSum('payments', 'amount_paid')
            ->orderBy('id', 'desc')
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'contact_number' => 'required|string|max:50',
            'email_address'  => 'nullable|email|max:100',
            'source'         => 'required|string|max:50',
            'package_tier'   => 'required|in:1500,2500,4000',
            'check_in_date'  => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
        ]);

        $checkIn  = Carbon::parse($validated['check_in_date'])->setTime(13, 0, 0);
        $checkOut = Carbon::parse($validated['check_out_date'])->setTime(12, 0, 0);

        if ($checkOut->lte($checkIn)) {
            $checkOut = $checkIn->copy()->addDay()->setTime(12, 0, 0);
        }

        $nights = max(1, $checkIn->copy()->startOfDay()->diffInDays($checkOut->copy()->startOfDay()));
        $packageRate         = (float) $validated['package_tier'];
        $totalAmount         = $packageRate * $nights;
        $requiredDownpayment = $totalAmount * 0.50;

        DB::transaction(function () use ($validated, $checkIn, $checkOut, $totalAmount, $requiredDownpayment) {
            $guest = Guest::firstOrCreate(
                [
                    'first_name'     => $validated['first_name'],
                    'last_name'      => $validated['last_name'],
                    'contact_number' => $validated['contact_number'],
                ],
                [
                    'email_address' => $validated['email_address'] ?? null,
                    'source'        => $validated['source'],
                ]
            );

            if (! $guest->wasRecentlyCreated) {
                $guest->update([
                    'email_address' => $validated['email_address'] ?? $guest->email_address,
                    'source'        => $validated['source'],
                ]);
            }

            Booking::create([
                'guest_id'             => $guest->id,
                'user_id'              => Auth::id(),
                'check_in_date'        => $checkIn,
                'check_out_date'       => $checkOut,
                'total_amount'         => $totalAmount,
                'required_downpayment' => $requiredDownpayment,
                'booking_status'       => 'pending',
            ]);
        });

        return redirect()->route('bookings.index')
            ->with('success', 'Booking logged successfully. Guest saved to Guest Directory. Awaiting mandatory 50% downpayment.');
    }

    public function recordPayment(Request $request, $id)
    {
        $booking = Booking::withSum('payments', 'amount_paid')->findOrFail($id);

        $validated = $request->validate([
            'amount_paid'       => 'required|numeric|min:0.01',
            'payment_method'    => 'required|in:cash,gcash,bank_transfer',
            'manual_receipt_no' => 'required|string|max:100',
        ]);

        $remaining = $booking->remaining_balance;

        if ($remaining <= 0) {
            return back()->withErrors(['amount_paid' => 'This booking is already fully paid.']);
        }

        // Enforce mandatory 50% downpayment on first payment
        if ($booking->booking_status === 'pending'
            && $booking->total_paid < $booking->required_downpayment) {

            $outstandingDownpayment = $booking->required_downpayment - $booking->total_paid;

            if ($validated['amount_paid'] < $outstandingDownpayment) {
                return back()->withInput()->withErrors([
                    'amount_paid' => 'The mandatory 50% downpayment requires at least ₱ '
                        . number_format($outstandingDownpayment, 2)
                        . '. Please collect the full downpayment before proceeding.',
                ]);
            }
        }

        // Reject overpayment
        if ($validated['amount_paid'] > $remaining) {
            return back()->withInput()->withErrors([
                'amount_paid' => 'Amount exceeds remaining balance of ₱ ' . number_format($remaining, 2) . '.',
            ]);
        }

        DB::transaction(function () use ($booking, $validated) {
            Payment::create([
                'booking_id'        => $booking->id,
                'guest_id'          => $booking->guest_id,
                'user_id'           => Auth::id(),
                'amount_paid'       => $validated['amount_paid'],
                'payment_type'      => $booking->booking_status === 'pending' ? 'downpayment' : 'balance',
                'payment_method'    => $validated['payment_method'],
                'manual_receipt_no' => $validated['manual_receipt_no'],
            ]);

            $booking->refresh()->loadSum('payments', 'amount_paid');
            $booking->recalculateStatus();
        });

        return redirect()->route('bookings.index')->with('success', 'Payment recorded successfully.');
    }
}