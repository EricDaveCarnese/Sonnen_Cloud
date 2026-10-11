<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
{
    $items          = InventoryItem::with('preferredSupplier')->orderBy('item_name')->get();
    $suppliers      = Supplier::orderBy('supplier_name')->get();
    $purchaseOrders = PurchaseOrder::with(['supplier', 'inventoryItem'])->orderBy('id', 'desc')->get();

    $movements = \App\Models\InventoryMovement::with(['inventoryItem', 'user'])
        ->orderBy('id', 'desc')
        ->take(100)
        ->get();

    return view('inventory.index', compact('items', 'suppliers', 'purchaseOrders', 'movements'));
}

    public function createPO(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'        => 'required|exists:suppliers,id',
            'inventory_item_id'  => 'required|exists:inventory_items,id',
            'quantity_ordered'   => 'required|numeric|min:0.1',
            'unit_price'         => 'required|numeric|min:0',
        ]);

        // Compute total on server side - do not trust client-submitted total
        $totalCost = $validated['quantity_ordered'] * $validated['unit_price'];

        // Optional: verify the item's preferred supplier matches
        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        if ($item->preferred_supplier_id && $item->preferred_supplier_id != $validated['supplier_id']) {
            return back()->withInput()->withErrors([
                'inventory_item_id' => 'The selected item is not supplied by this supplier.',
            ]);
        }

        PurchaseOrder::create([
            'supplier_id'           => $validated['supplier_id'],
            'inventory_item_id'     => $validated['inventory_item_id'],
            'user_id'               => Auth::id(),
            'order_date'            => now(),
            'quantity_ordered'      => $validated['quantity_ordered'],
            'total_cost'            => $totalCost,
            'purchase_order_status' => 'pending',
        ]);

        return redirect()->route('po.index')->with('success', 'Purchase order created.');
    }

    public function receivePO($id)
{
    $po = PurchaseOrder::with('inventoryItem')->findOrFail($id);

    if ($po->purchase_order_status === 'pending') {
        DB::transaction(function () use ($po) {
            $po->update(['purchase_order_status' => 'received']);

            $stock = $po->inventoryItem;
            $stock->increment('quantity_on_hand', $po->quantity_ordered);
            $stock->refresh();

            \App\Models\InventoryMovement::create([
                'inventory_item_id' => $stock->id,
                'user_id'           => Auth::id(),
                'movement_type'     => 'po_received',
                'quantity_change'   => $po->quantity_ordered,
                'quantity_after'    => $stock->quantity_on_hand,
                'reference'         => 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                'notes'             => "Received from {$po->supplier->supplier_name}",
            ]);
        });
    }

    return redirect()->route('po.index')->with('success', 'Delivery received. Stock levels updated.');
}
}