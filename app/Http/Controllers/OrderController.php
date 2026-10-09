<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::with('inventoryItem')->get();

        $activeOrders = Order::with('orderItems.menuItem')
            ->whereIn('order_status', ['pending', 'preparing', 'served'])
            ->orderBy('id', 'desc')
            ->get();

        return view('orders.index', compact('menuItems', 'activeOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
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

                foreach ($validated['items'] as $itemData) {
                    $menuItem = MenuItem::with('inventoryItem')->findOrFail($itemData['menu_item_id']);
                    $subtotal = $menuItem->unit_price * $itemData['quantity'];
                    $totalPrice += $subtotal;

                    if ($menuItem->inventory_item_id && $menuItem->inventoryItem) {
                        $requiredQty = $menuItem->ingredient_qty_per_order * $itemData['quantity'];
                        $stock = $menuItem->inventoryItem;

                        if ($stock->quantity_on_hand < $requiredQty) {
                            throw new Exception("Insufficient stock for item: {$menuItem->item_name}. Available: {$stock->quantity_on_hand} {$stock->unit_of_measure}");
                        }

                        $stock->decrement('quantity_on_hand', $requiredQty);
                    }

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'menu_item_id' => $menuItem->id,
                        'quantity'     => $itemData['quantity'],
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

        if ($order->order_status === 'paid') {
            return redirect()->route('orders.index')->withErrors(['payment' => 'This order has already been settled.']);
        }

        $validated = $request->validate([
            'payment_method'    => 'required|in:cash,gcash,bank_transfer',
            'manual_receipt_no' => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($order, $validated) {
            Payment::create([
                'order_id'          => $order->id,
                'user_id'           => Auth::id(),
                'amount_paid'       => $order->total_price,
                'payment_type'      => 'full_payment',
                'payment_method'    => $validated['payment_method'],
                'manual_receipt_no' => $validated['manual_receipt_no'],
            ]);

            $order->update(['order_status' => 'paid']);
        });

        return redirect()->route('orders.index')->with('success', 'Transaction settled and digital receipt logged.');
    }
}