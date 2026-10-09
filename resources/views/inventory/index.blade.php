@extends('layouts.app')

@section('page-title', 'Inventory Items')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<!-- Add Inventory Item Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Add New Stock Commodity</h3>
    </div>
    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr; gap: 14px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Item Name</label>
                <input type="text" name="item_name" class="form-input" placeholder="e.g. Arabica Beans" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Category</label>
                <input type="text" name="category" class="form-input" placeholder="Raw Ingredient, Supply, etc." required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Initial Stock</label>
                <input type="number" step="0.01" name="quantity_on_hand" class="form-input" value="0.00" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Unit of Measure</label>
                <input type="text" name="unit_of_measure" class="form-input" placeholder="kg, pcs, liters" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Reorder Alert Level</label>
                <input type="number" step="0.01" name="reorder_level" class="form-input" value="5.00" required />
            </div>
        </div>
        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Add Stock Item</button>
        </div>
    </form>
</div>

<!-- Inventory Stock Table -->
<div class="table-card">
    <div class="top-table">
        <h3>Raw Ingredient & Supply Levels</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity On Hand</th>
                    <th>Unit</th>
                    <th>Reorder Level</th>
                    <th>Stock Health</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>INV-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $item->item_name }}</strong></td>
                    <td>{{ $item->category }}</td>
                    <td>{{ number_format($item->quantity_on_hand, 2) }}</td>
                    <td>{{ $item->unit_of_measure }}</td>
                    <td>{{ number_format($item->reorder_level, 2) }}</td>
                    <td>
                        @if($item->quantity_on_hand <= 0)
                            <a href="{{ route('po.index', ['item' => $item->id]) }}"
                               class="room-tag"
                               style="background-color: #7f1d1d; color: #fff; text-decoration: none; cursor: pointer;"
                               title="Click to create a purchase order for this item">
                                Out of Stock
                            </a>
                        @elseif($item->quantity_on_hand <= $item->reorder_level)
                            <a href="{{ route('po.index', ['item' => $item->id]) }}"
                               class="room-tag"
                               style="background-color: #ef4444; color: #fff; text-decoration: none; cursor: pointer;"
                               title="Click to create a purchase order for this item">
                                Low Stock
                            </a>
                        @else
                            <span class="room-tag" style="background-color: #10b981; color: #fff;">In Stock</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">No inventory items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection