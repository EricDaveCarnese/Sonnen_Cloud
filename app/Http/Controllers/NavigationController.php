<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guest;
use App\Models\User;
use App\Models\Payment;
use App\Models\MenuItem;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Hash;

class NavigationController extends Controller
{
    // --- GUESTS ---
    public function guests()
    {
        $guests = Guest::withCount('bookings')->orderBy('id', 'desc')->get();
        return view('modules.guests', compact('guests'));
    }

    // --- USERS ---
   public function users()
    {
        $users     = User::orderBy('id', 'desc')->get();
        $employees = \App\Models\Employee::orderBy('id', 'desc')->get();

        return view('modules.users', compact('users', 'employees'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:100',
            'role'     => 'required|in:admin,operations,owner_manager',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'fullname' => $validated['fullname'],
            'username' => $validated['username'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status'   => 'active',
        ]);

        return redirect()->route('users.index')->with('success', 'Staff account created successfully.');
    }

    // --- PAYMENTS ---
    public function payments()
{
    $payments = Payment::with(['booking.guest', 'order', 'daytourBooking', 'user'])
        ->orderBy('id', 'desc')
        ->get();

    $hotDrinks  = \App\Models\MenuItem::where('category', 'Hot Drinks')->orderBy('item_name')->get();
    $coldDrinks = \App\Models\MenuItem::where('category', 'Cold Drinks')->orderBy('item_name')->get();
    $allPaidItems = \App\Models\MenuItem::whereNotIn('category', ['Breakfast Set Meal', 'Rice'])
        ->orderBy('category')
        ->orderBy('item_name')
        ->get();

    return view('modules.payments', compact('payments', 'hotDrinks', 'coldDrinks', 'allPaidItems'));
}
    // --- MENU ITEMS ---
    public function menu()
{
    $menuItems = MenuItem::with('ingredients.inventoryItem')
        ->orderBy('category')
        ->orderBy('item_name')
        ->get();

    $categories = $menuItems->pluck('category')->unique()->sort()->values();
    $inventoryItems = InventoryItem::orderBy('item_name')->get();

    return view('modules.menu', compact('menuItems', 'categories', 'inventoryItems'));
}

public function storeMenuItem(Request $request)
{
    $validated = $request->validate([
        'item_name'                          => 'required|string|max:255',
        'category'                           => 'required|string|max:100',
        'unit_price'                         => 'required|numeric|min:0',
        'ingredients'                        => 'nullable|array',
        'ingredients.*.inventory_item_id'    => 'required|exists:inventory_items,id',
        'ingredients.*.quantity_per_serving' => 'required|numeric|min:0.001',
    ]);

    \DB::transaction(function () use ($validated) {
        $menuItem = MenuItem::create([
            'item_name'  => $validated['item_name'],
            'category'   => $validated['category'],
            'unit_price' => $validated['unit_price'],
        ]);

        $ingredients = $validated['ingredients'] ?? [];
        foreach ($ingredients as $ing) {
            $menuItem->ingredients()->create([
                'inventory_item_id'    => $ing['inventory_item_id'],
                'quantity_per_serving' => $ing['quantity_per_serving'],
            ]);
        }
    });

    return redirect()->route('menu.index')->with('success', 'Menu item added successfully.');
}

public function updateMenuItem(Request $request, $id)
{
    $validated = $request->validate([
        'item_name'                          => 'required|string|max:255',
        'category'                           => 'required|string|max:100',
        'unit_price'                         => 'required|numeric|min:0',
        'ingredients'                        => 'nullable|array',
        'ingredients.*.inventory_item_id'    => 'required|exists:inventory_items,id',
        'ingredients.*.quantity_per_serving' => 'required|numeric|min:0.001',
    ]);

    $menuItem = MenuItem::findOrFail($id);

    \DB::transaction(function () use ($menuItem, $validated) {
        $menuItem->update([
            'item_name'  => $validated['item_name'],
            'category'   => $validated['category'],
            'unit_price' => $validated['unit_price'],
        ]);

        // Replace all ingredients
        $menuItem->ingredients()->delete();

        $ingredients = $validated['ingredients'] ?? [];
        foreach ($ingredients as $ing) {
            $menuItem->ingredients()->create([
                'inventory_item_id'    => $ing['inventory_item_id'],
                'quantity_per_serving' => $ing['quantity_per_serving'],
            ]);
        }
    });

    return redirect()->route('menu.index')->with('success', 'Menu item updated successfully.');
}
    // --- INVENTORY ITEMS ---
    public function storeInventoryItem(Request $request)
{
    $validated = $request->validate([
        'item_name'             => 'required|string|max:255',
        'category'              => 'required|string|max:100',
        'quantity_on_hand'      => 'required|numeric|min:0',
        'unit_of_measure'       => 'required|string|max:50',
        'reorder_level'         => 'required|numeric|min:0',
        'preferred_supplier_id' => 'nullable|exists:suppliers,id',
    ]);

    InventoryItem::create($validated);

    return redirect()->route('inventory.index')->with('success', 'Inventory stock item created.');
}

    // --- SUPPLIERS ---
    public function suppliers()
    {
        $suppliers = Supplier::withCount('purchaseOrders')->orderBy('supplier_name')->get();
        return view('modules.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'supplier_name'  => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number'   => 'required|string|max:50',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier registered successfully.');
    }

    // --- PURCHASE ORDERS ---
    public function purchaseOrders()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'inventoryItem', 'user'])->orderBy('id', 'desc')->get();
        $suppliers      = Supplier::orderBy('supplier_name')->get();
        $inventoryItems = InventoryItem::orderBy('item_name')->get();

        return view('modules.purchase_orders', compact('purchaseOrders', 'suppliers', 'inventoryItems'));
    }
}