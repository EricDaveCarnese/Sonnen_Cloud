<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DaytourBooking;
use Illuminate\Support\Facades\Auth;

class DaytourController extends Controller
{
    public function index()
    {
        $daytours = DaytourBooking::with('user')->orderBy('id', 'desc')->get();
        $menu     = config('breakfast_menu');

        return view('daytour.index', compact('daytours', 'menu'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name'                  => 'required|string|max:255',
            'contact_number'              => 'required|string|max:50',
            'pax'                         => 'required|integer|min:1|max:50',
            'payment_method'              => 'required|in:cash,gcash,bank_transfer',
            'manual_receipt_no'           => 'required|string|max:100',
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

        DaytourBooking::create([
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
            'manual_receipt_no' => $validated['manual_receipt_no'],
        ]);

        return redirect()->route('daytour.index')
            ->with('success', "Day tour registered. {$pax} pax @ ₱100/person. Total: ₱" . number_format($totalAmount, 2) . " collected.");
    }
}