<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DaytourBooking;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Support\ReceiptNumberGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DaytourController extends Controller
{
    public function index()
    {
        $daytours   = DaytourBooking::with('user')->orderBy('id', 'desc')->get();
        $hotDrinks  = MenuItem::where('category', 'Hot Drinks')->orderBy('item_name')->get();
        $coldDrinks = MenuItem::where('category', 'Cold Drinks')->orderBy('item_name')->get();
        $allPaidItems = MenuItem::whereNotIn('category', ['Breakfast Set Meal', 'Rice'])
            ->orderBy('category')
            ->orderBy('item_name')
            ->get();

        return view('daytour.index', compact('daytours', 'hotDrinks', 'coldDrinks', 'allPaidItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name'                  => 'required|string|max:255',
            'contact_number'              => 'required|string|max:50',
            'pax'                         => 'required|integer|min:1|max:50',
            'payment_method'              => 'required|in:cash,gcash,bank_transfer',
            'inclusions'                  => 'required|array',
            'inclusions.*.drink_type'     => 'required|in:hot,cold',
            'inclusions.*.drink_name'     => 'required|string|max:255',
            'extra_orders'                => 'nullable|array',
        ]);

        $pax           = (int) $validated['pax'];
        $entranceTotal = 100.00 * $pax;
        $extraAmount   = 0.00;
        $extraOrders   = $validated['extra_orders'] ?? [];
        $cleanExtras   = [];

        foreach ($extraOrders as $extra) {
            if (! empty($extra['name']) && isset($extra['quantity'], $extra['price']) && (int) $extra['quantity'] > 0) {
                $subtotal     = (float) $extra['price'] * (int) $extra['quantity'];
                $extraAmount += $subtotal;
                $cleanExtras[] = [
                    'name'     => $extra['name'],
                    'quantity' => (int) $extra['quantity'],
                    'price'    => (float) $extra['price'],
                    'subtotal' => $subtotal,
                ];
            }
        }

        $totalAmount = $entranceTotal + $extraAmount;
        $receiptNo   = ReceiptNumberGenerator::next();

        DB::transaction(function () use ($validated, $pax, $entranceTotal, $extraAmount, $cleanExtras, $totalAmount, $receiptNo) {
            $daytour = DaytourBooking::create([
                'user_id'           => Auth::id(),
                'guest_name'        => $validated['guest_name'],
                'contact_number'    => $validated['contact_number'],
                'pax'               => $pax,
                'entrance_fee'      => $entranceTotal,
                'extra_amount'      => $extraAmount,
                'total_amount'      => $totalAmount,
                'inclusions'        => $validated['inclusions'],
                'extra_orders'      => empty($cleanExtras) ? null : $cleanExtras,
                'payment_method'    => $validated['payment_method'],
                'manual_receipt_no' => $receiptNo,
            ]);

            Payment::create([
                'daytour_booking_id' => $daytour->id,
                'user_id'            => Auth::id(),
                'amount_paid'        => $totalAmount,
                'payment_type'       => 'full_payment',
                'payment_method'     => $validated['payment_method'],
                'manual_receipt_no'  => $receiptNo,
            ]);
        });

        return redirect()->route('payments.index')
            ->with('success', "Day tour registered. {$pax} pax @ ₱100/person. Total: ₱" . number_format($totalAmount, 2) . " collected.");
    }
}