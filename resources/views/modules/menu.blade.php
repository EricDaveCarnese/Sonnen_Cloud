@extends('layouts.app')

@section('page-title', 'Menu Items')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<!-- Add Menu Item Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Add New Menu Offering</h3>
    </div>
    <form action="{{ route('menu.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr 2fr 1fr; gap: 14px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Dish / Drink Name</label>
                <input type="text" name="item_name" class="form-input" placeholder="e.g. Cafe Latte" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Category</label>
                <input type="text" name="category" class="form-input" placeholder="Beverage, Main Course, etc." required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" class="form-input" placeholder="150.00" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Linked Raw Stock</label>
                <select name="inventory_item_id" class="form-input select-dark">
                    <option value="">None (No Deduction)</option>
                    @foreach($inventoryItems as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->item_name }} ({{ $inv->unit_of_measure }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Qty per Order</label>
                <input type="number" step="0.001" name="ingredient_qty_per_order" class="form-input" value="1.000" required />
            </div>
        </div>
        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Save Menu Item</button>
        </div>
    </form>
</div>

<!-- Menu Catalog Grouped by Category -->
@forelse($menuByCategory as $category => $items)
<div class="table-card" style="margin-bottom: 20px;">
    <div class="top-table">
        <h3>{{ $category }} <span style="color: var(--text-muted); font-size: 13px; font-weight: 400;">({{ $items->count() }} item{{ $items->count() === 1 ? '' : 's' }})</span></h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dish / Drink Name</th>
                    <th>Category</th>
                    <th>Selling Price</th>
                    <th>Linked Inventory Ingredient</th>
                    <th>Deduction per Order</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>MNU-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $item->item_name }}</strong></td>
                    <td><span class="room-tag">{{ $item->category }}</span></td>
                    <td>₱ {{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->inventoryItem->item_name ?? 'None' }}</td>
                    <td>{{ $item->ingredient_qty_per_order }} {{ $item->inventoryItem->unit_of_measure ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="table-card">
    <div class="table-container">
        <table>
            <tbody>
                <tr><td>No menu items configured.</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endforelse
@endsection