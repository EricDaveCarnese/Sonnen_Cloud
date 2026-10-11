<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BreakfastOrder;
use App\Models\MenuItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BreakfastController extends Controller
{
    public function selectBreakfast($bookingId)
    {
        $booking = Booking::with(['guest', 'payments', 'breakfastOrder'])
            ->withSum('payments', 'amount_paid')
            ->findOrFail($bookingId);

        if ($booking->booking_status !== 'confirmed') {
            return redirect()->route('bookings.index')
                ->with('error', 'Breakfast can only be selected for confirmed bookings when paying balance.');
        }

        if ($booking->breakfastOrder) {
            return redirect()->route('bookings.index')
                ->with('error', 'Breakfast has already been ordered for this booking.');
        }

        $setMeals   = MenuItem::whereIn('category', ['Breakfast Set Meal', 'Set Meal', 'Breakfast'])->orderBy('item_name')->get();
        $hotDrinks  = MenuItem::whereIn('category', ['Hot Drinks', 'Hot Drink'])->orderBy('item_name')->get();
        $coldDrinks = MenuItem::whereIn('category', ['Cold Drinks', 'Cold Drink'])->orderBy('item_name')->get();
        $riceItems  = MenuItem::whereIn('category', ['Rice', 'rice'])->orderBy('item_name')->get();
        $allPaidItems = MenuItem::whereNotIn('category', ['Breakfast Set Meal', 'Rice'])
            ->orderBy('category')
            ->orderBy('item_name')
            ->get();

        $hasAnyItems = $setMeals->count() > 0
            || $hotDrinks->count() > 0
            || $coldDrinks->count() > 0;

        return view('breakfast.select', compact(
            'booking',
            'setMeals',
            'hotDrinks',
            'coldDrinks',
            'riceItems',
            'allPaidItems',
            'hasAnyItems'
        ));
    }

    public function submitBreakfastAndPayBalance(Request $request, $bookingId)
{
    $booking = Booking::withSum('payments', 'amount_paid')->findOrFail($bookingId);

    if ($booking->breakfastOrder) {
        return redirect()->route('bookings.index')
            ->with('error', 'Breakfast has already been ordered for this booking.');
    }

    $validated = $request->validate([
        'amount_paid'          => 'required|numeric|min:0.01',
        'payment_method'       => 'required|in:cash,gcash,bank_transfer',
        'persons'              => 'required|array|min:1',
        'persons.*.main_dish'  => 'required|string|max:255',
        'persons.*.rice'       => 'required|string|max:255',
        'persons.*.drink_type' => 'required|in:hot,cold',
        'persons.*.drink_name' => 'required|string|max:255',
        'extra_items'          => 'nullable|array',
    ]);

    $remaining = $booking->remaining_balance;

    if ($validated['amount_paid'] > $remaining) {
        return back()->withInput()->withErrors([
            'amount_paid' => 'Amount exceeds remaining balance of ₱ ' . number_format($remaining, 2) . '.',
        ]);
    }

    $extraAmount = 0.00;
    $extraItems  = $validated['extra_items'] ?? [];
    $cleanExtras = [];

    foreach ($extraItems as $extra) {
        if (! empty($extra['name']) && isset($extra['quantity']) && (int) $extra['quantity'] > 0 && isset($extra['price'])) {
            $subtotal      = (float) $extra['price'] * (int) $extra['quantity'];
            $extraAmount  += $subtotal;
            $cleanExtras[] = [
                'name'     => $extra['name'],
                'quantity' => (int) $extra['quantity'],
                'price'    => (float) $extra['price'],
                'subtotal' => $subtotal,
                'variant'  => $extra['variant'] ?? null,
            ];
        }
    }

    try {
        DB::transaction(function () use ($booking, $validated, $extraAmount, $cleanExtras) {
            $reference = 'BK-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);

            // 1. Balance payment
            Payment::create([
                'booking_id'        => $booking->id,
                'guest_id'          => $booking->guest_id,
                'user_id'           => Auth::id(),
                'amount_paid'       => $validated['amount_paid'],
                'payment_type'      => 'balance',
                'payment_method'    => $validated['payment_method'],
                'manual_receipt_no' => \App\Support\ReceiptNumberGenerator::next(),
            ]);

            // 2. Deduct inventory for set meals and drinks per person
            foreach ($validated['persons'] as $i => $person) {
                $personNum = $i + 1;

                // Set meal
                $mealItem = MenuItem::where('item_name', $person['main_dish'])->first();
                if ($mealItem) {
                    \App\Services\InventoryDeductor::deductForMenuItem(
                        $mealItem,
                        1,
                        $reference,
                        "Breakfast P{$personNum} set meal: {$person['main_dish']}"
                    );
                }

                // Drink
                $drinkItem = MenuItem::where('item_name', $person['drink_name'])->first();
                if ($drinkItem) {
                    \App\Services\InventoryDeductor::deductForMenuItem(
                        $drinkItem,
                        1,
                        $reference,
                        "Breakfast P{$personNum} drink: {$person['drink_name']}"
                    );
                }
            }

            // 3. Deduct paid extras
            foreach ($cleanExtras as $extra) {
                $extraItem = MenuItem::where('item_name', $extra['name'])->first();
                if ($extraItem) {
                    \App\Services\InventoryDeductor::deductForMenuItem(
                        $extraItem,
                        $extra['quantity'],
                        $reference,
                        "Breakfast extra: {$extra['name']} x{$extra['quantity']}"
                    );
                }
            }

            // 4. Save breakfast order record
            BreakfastOrder::create([
                'booking_id'      => $booking->id,
                'user_id'         => Auth::id(),
                'pax'             => count($validated['persons']),
                'order_details'   => ['persons' => $validated['persons'], 'extras' => $cleanExtras],
                'included_amount' => 0.00,
                'extra_amount'    => $extraAmount,
                'total_amount'    => $extraAmount,
                'status'          => 'pending',
            ]);

            $booking->refresh()->loadSum('payments', 'amount_paid');
            $booking->recalculateStatus();
        });
    } catch (\Exception $e) {
        return back()->withInput()->withErrors(['stock_error' => $e->getMessage()]);
    }

    return redirect()->route('bookings.index')
        ->with('success', 'Balance payment recorded and breakfast order saved successfully.');
}

    public function advanceStatus(Request $request, $id)
    {
        $newStatus = $request->input('new_status');
        $allowed = ['preparing', 'served'];

        if (! in_array($newStatus, $allowed)) {
            return back()->withErrors(['status' => 'Invalid status transition.']);
        }

        $order = BreakfastOrder::findOrFail($id);

        // Enforce forward-only transitions
        $currentIndex = array_search($order->status, ['pending', 'preparing', 'served']);
        $targetIndex  = array_search($newStatus, ['pending', 'preparing', 'served']);

        if ($targetIndex === false || $currentIndex === false || $targetIndex <= $currentIndex) {
            return back()->withErrors(['status' => 'Cannot move breakfast order to that status.']);
        }

        $order->update(['status' => $newStatus]);

        return redirect()->route('orders.index')
            ->with('success', 'Breakfast order marked as ' . $newStatus . '.');
    }
}