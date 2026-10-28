<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DaytourBooking;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\ActivityLog; // <--- ADDED IMPORT
use App\Support\ReceiptNumberGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DaytourController extends Controller
{
    public function index()
    {
        $daytours   = DaytourBooking::with('user')->orderBy('id', 'desc')->get();
        $hotDrinks  = MenuItem::with('ingredients.inventoryItem')
            ->where('category', 'Hot Drinks')->orderBy('item_name')->get();
        $coldDrinks = MenuItem::with('ingredients.inventoryItem')
            ->where('category', 'Cold Drinks')->orderBy('item_name')->get();
        $allPaidItems = MenuItem::with('ingredients.inventoryItem')
            ->whereNotIn('category', ['Breakfast Set Meal', 'Rice'])
            ->orderBy('category')
            ->orderBy('item_name')
            ->get();

        return view('daytour.index', compact('daytours', 'hotDrinks', 'coldDrinks', 'allPaidItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'nullable|in:cash,gcash,bank_transfer',
        ]);
        
        $paymentMethod = $validated['payment_method'] ?? 'cash';
        $entranceTotal = 100.00; 
        $receiptNo     = ReceiptNumberGenerator::next();

        try {
            DB::transaction(function () use ($paymentMethod, $entranceTotal, $receiptNo) {
                
                // ONLY look for tickets created TODAY
                $lastTicket = DaytourBooking::whereDate('created_at', \Carbon\Carbon::today())
                    ->max('ticket_no') ?? 0;
                    
                $nextTicket = $lastTicket + 1;

                $daytour = DaytourBooking::create([
                    'ticket_no'         => $nextTicket,
                    'user_id'           => Auth::id(),
                    'guest_name'        => 'Walk-in Guest',
                    'contact_number'    => 'N/A',
                    'pax'               => 1, 
                    'entrance_fee'      => $entranceTotal,
                    'extra_amount'      => 0.00,
                    'total_amount'      => $entranceTotal,
                    'inclusions'        => null, 
                    'extra_orders'      => null,
                    'payment_method'    => $paymentMethod,
                    'manual_receipt_no' => $receiptNo,
                    'status'            => 'pending',
                ]);

                // Record the Payment in the Ledger
                Payment::create([
                    'daytour_booking_id' => $daytour->id,
                    'user_id'            => Auth::id(),
                    'amount_paid'        => $entranceTotal,
                    'payment_type'       => 'full_payment',
                    'payment_method'     => $paymentMethod,
                    'manual_receipt_no'  => $receiptNo,
                ]);

                // Log the action AFTER the ticket is created
                ActivityLog::record('Issued Ticket', "Issued Day Tour Ticket #{$nextTicket}");
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('payments.index')
            ->with('success', "Ticket issued successfully! Please tell the guest to proceed to the Canteen.");
    }

    public function showRedeem($id)
    {
        $daytour = DaytourBooking::findOrFail($id);

        if ($daytour->status === 'served') {
            return redirect()->route('orders.index')->with('error', 'This ticket has already been redeemed.');
        }

        $hotDrinks  = MenuItem::with('ingredients.inventoryItem')->whereIn('category', ['Hot Drinks', 'Hot Drink'])->get();
        $coldDrinks = MenuItem::with('ingredients.inventoryItem')->whereIn('category', ['Cold Drinks', 'Cold Drink'])->get();

        return view('daytour.redeem', compact('daytour', 'hotDrinks', 'coldDrinks'));
    }

    public function submitRedeem(Request $request, $id)
    {
        $daytour = DaytourBooking::findOrFail($id);

        if ($daytour->status === 'served') {
            return back()->withErrors(['error' => 'Ticket already redeemed.']);
        }

        $validated = $request->validate([
            'inclusions'                  => 'required|array|min:' . $daytour->pax,
            'inclusions.*.drink_type'     => 'required|in:hot,cold',
            'inclusions.*.drink_name'     => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($daytour, $validated) {
                $reference = 'DT-' . str_pad($daytour->ticket_no, 4, '0', STR_PAD_LEFT);

                // 1. Create linked order for F&B kitchen tracking
                $order = \App\Models\Order::create([
                    'user_id'            => Auth::id(),
                    'daytour_booking_id' => $daytour->id,
                    'total_price'        => 0.00,
                    'order_status'       => 'pending',
                ]);

                // 2. Deduct inventory and create order items for each drink
                foreach ($validated['inclusions'] as $i => $inc) {
                    $personNum = $i + 1;

                    // Drink
                    $drinkItem = MenuItem::where('item_name', $inc['drink_name'])->first();
                    if ($drinkItem) {
                        \App\Services\InventoryDeductor::deductForMenuItem(
                            $drinkItem, 1, $reference, "Day Tour Ticket #{$daytour->ticket_no} P{$personNum} drink: {$inc['drink_name']}"
                        );
                        \App\Models\OrderItem::create([
                            'order_id'     => $order->id,
                            'menu_item_id' => $drinkItem->id,
                            'quantity'     => 1,
                            'unit_price'   => 0.00,
                            'subtotal'     => 0.00,
                        ]);
                    }

                    // Suman
                    $sumanItem = MenuItem::whereIn('item_name', ['Suman', 'Suman (Malagkit)'])->first();
                    if ($sumanItem) {
                        \App\Services\InventoryDeductor::deductForMenuItem(
                            $sumanItem, 1, $reference, "Day Tour Ticket #{$daytour->ticket_no} P{$personNum} suman"
                        );
                        \App\Models\OrderItem::create([
                            'order_id'     => $order->id,
                            'menu_item_id' => $sumanItem->id,
                            'quantity'     => 1,
                            'unit_price'   => 0.00,
                            'subtotal'     => 0.00,
                        ]);
                    }
                }

                // 3. Mark ticket as served
                $daytour->update([
                    'inclusions' => $validated['inclusions'],
                    'status'     => 'served'
                ]);

                // Log the redemption
                ActivityLog::record('Redeemed Ticket', "Redeemed drinks for Day Tour Ticket #{$daytour->ticket_no}");
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['stock_error' => $e->getMessage()]);
        }

        return redirect()->route('orders.index')->with('success', "Drinks for Ticket #{$daytour->ticket_no} redeemed and sent to kitchen!");
    }
}