<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller {
    public function index() {
        $items = InventoryItem::all();
        $suppliers = Supplier::all();
        $purchaseOrders = PurchaseOrder::with(['supplier', 'inventoryItem'])->get();
        return view('inventory.index', compact('items', 'suppliers', 'purchaseOrders'));
    }

    public function createPO(Request $request) {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity_ordered' => 'required|numeric|min:0.1',
            'total_cost' => 'required|numeric|min:0',
        ]);

        PurchaseOrder::create([
            'supplier_id' => $validated['supplier_id'],
            'inventory_item_id' => $validated['inventory_item_id'],
            'user_id' => Auth::id(),
            'order_date' => now(),
            'quantity_ordered' => $validated['quantity_ordered'],
            'total_cost' => $validated['total_cost'],
            'purchase_order_status' => 'pending',
        ]);

        return redirect()->route('inventory.index')->with('success', 'Purchase order created.');
    }

    public function receivePO($id) {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->purchase_order_status === 'pending') {
            DB::transaction(function () use ($po) {
                $po->update(['purchase_order_status' => 'received']);
                $po->inventoryItem->increment('quantity_on_hand', $po->quantity_ordered);
            });
        }

        return redirect()->route('inventory.index')->with('success', 'Delivery received. Stock levels updated.');
    }
}
