<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\BreakfastOrder;
use App\Services\InventoryDeductor;
use App\Support\ReceiptNumberGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::with(['ingredients.inventoryItem'])->get();

        // Walk-in orders only (no booking, no day tour)
        $activeOrders = Order::with('orderItems.menuItem')
            ->whereIn('order_status', ['pending', 'preparing', 'served'])
            ->whereNull('booking_id')
            ->whereNull('daytour_booking_id')
            ->orderBy('id', 'desc')
            ->get();

        // Breakfast orders (separate table)
        $activeBreakfasts = BreakfastOrder::with(['booking.guest', 'user'])
            ->whereIn('status', ['pending', 'preparing'])
            ->orderBy('id', 'desc')
            ->get();

        // Day tour orders (regular orders linked to daytours)
        $activeDaytours = Order::with(['orderItems.menuItem', 'daytourBooking'])
            ->whereNotNull('daytour_booking_id')
            ->whereIn('order_status', ['pending', 'preparing'])
            ->orderBy('id', 'desc')
            ->get();

        return view('orders.index', compact('menuItems', 'activeOrders', 'activeBreakfasts', 'activeDaytours'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $order = Order::create([
                    'user_id'      => Auth::id(),
                    'total_price'  => 0.00,
                    'order_status' => 'pending',
                ]);

                $totalPrice = 0;
                $reference  = 'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);

                foreach ($validated['items'] as $itemData) {
                    $menuItem = MenuItem::with('ingredients.inventoryItem')->findOrFail($itemData['menu_item_id']);
                    $qty      = (int) $itemData['quantity'];

                    $subtotal    = $menuItem->unit_price * $qty;
                    $totalPrice += $subtotal;

                    InventoryDeductor::deductForMenuItem(
                        $menuItem,
                        $qty,
                        $reference,
                        "For {$menuItem->item_name} x{$qty}"
                    );

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'menu_item_id' => $menuItem->id,
                        'quantity'     => $qty,
                        'unit_price'   => $menuItem->unit_price,
                        'subtotal'     => $subtotal,
                    ]);
                }

                $order->update(['total_price' => $totalPrice]);
            });

            return redirect()->route('orders.index')->with('success', 'Order created and inventory deducted successfully.');
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['stock_error' => $e->getMessage()]);
        }
    }

    public function processPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Guard: day tour orders are paid at registration, cannot be paid again
        if ($order->daytour_booking_id) {
            return redirect()->route('orders.index')
                ->withErrors(['payment' => 'Day tour orders are paid at registration and cannot be settled separately.']);
        }

        if ($order->order_status === 'paid') {
            return redirect()->route('orders.index')->withErrors(['payment' => 'This order has already been settled.']);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
        ]);

        DB::transaction(function () use ($order, $validated) {
            Payment::create([
                'order_id'          => $order->id,
                'user_id'           => Auth::id(),
                'amount_paid'       => $order->total_price,
                'payment_type'      => 'full_payment',
                'payment_method'    => $validated['payment_method'],
                'manual_receipt_no' => ReceiptNumberGenerator::next(),
            ]);

            $order->update(['order_status' => 'paid']);
        });

        return redirect()->route('orders.index')->with('success', 'Transaction settled and digital receipt logged.');
    }

    public function advanceStatus(Request $request, $id)
    {
        $newStatus = $request->input('new_status');
        $allowed   = ['preparing', 'served'];

        if (! in_array($newStatus, $allowed)) {
            return back()->withErrors(['status' => 'Invalid status transition.']);
        }

        $order = Order::findOrFail($id);

        if ($order->order_status === 'paid') {
            return back()->withErrors(['status' => 'This order is already settled.']);
        }

        $currentIndex = array_search($order->order_status, ['pending', 'preparing', 'served']);
        $targetIndex  = array_search($newStatus, ['pending', 'preparing', 'served']);

        if ($targetIndex === false || $currentIndex === false || $targetIndex <= $currentIndex) {
            return back()->withErrors(['status' => 'Cannot move order to that status.']);
        }

        $order->update(['order_status' => $newStatus]);

        return redirect()->route('orders.index')
            ->with('success', 'Order marked as ' . $newStatus . '.');
    }
}