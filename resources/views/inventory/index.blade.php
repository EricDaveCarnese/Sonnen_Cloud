@extends('layouts.app')

@section('page-title', 'Inventory Items')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">
        {{ $errors->first() }}
    </div>
@endif

<!-- Add Inventory Item Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Add New Stock Commodity</h3>
    </div>
    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr 1.5fr; gap: 14px; align-items: end;">
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
                <input type="text" name="unit_of_measure" class="form-input" placeholder="g, ml, pcs" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Reorder Level</label>
                <input type="number" step="0.01" name="reorder_level" class="form-input" value="5.00" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Preferred Supplier</label>
                <select name="preferred_supplier_id" class="form-input select-dark">
                    <option value="">-- None --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Add Item</button>
        </div>
    </form>
</div>

<!-- Inventory Stock Table -->
<div class="table-card">
    <div class="top-table">
        <h3>Raw Ingredient & Supply Levels</h3>
        <div style="display:flex; gap:10px; align-items:center;">
            <label style="font-size: 13px; color: var(--text-muted);">Filter:</label>
            <select id="inventory-category-filter" class="form-input select-dark" style="margin:0; padding: 6px 12px; min-width: 200px;" onchange="filterInventory()">
                <option value="">All Categories ({{ $items->count() }})</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }} ({{ $items->where('category', $cat)->count() }})</option>
                @endforeach
            </select>
        </div>
    </div>
     <div class="table-container" style="max-height: 600px; overflow-y: auto;">
        <table>
            <thead style="position: sticky; top: 0; z-index: 2;">
                <tr>
                    <th>Item ID</th>
                    <th class="text-left">Item Name</th>
                    <th class="text-left">Category</th>
                    <th class="text-right">Quantity On Hand</th>
                    <th>Unit</th>
                    <th class="text-right">Reorder Level</th>
                    <th class="text-left">Preferred Supplier</th>
                    <th>Stock Health</th>
                </tr>
            </thead>
            <tbody id="inventory-table-body">
                @forelse($items as $item)
                <tr data-category="{{ $item->category }}">
                    <td>INV-{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left"><strong>{{ $item->item_name }}</strong></td>
                    <td class="text-left">{{ $item->category }}</td>
                    <td class="text-right">{{ number_format($item->quantity_on_hand, 2) }}</td>
                    <td>{{ $item->unit_of_measure }}</td>
                    <td class="text-right">{{ number_format($item->reorder_level, 2) }}</td>
                    <td class="text-left">{{ $item->preferredSupplier->supplier_name ?? '—' }}</td>
                    <td>
                        @php $isOwner = Auth::user()->role === 'owner_manager'; @endphp
                        @if($item->quantity_on_hand <= 0)
                            @if($isOwner)
                                <a href="{{ route('po.index', ['item' => $item->id]) }}"
                                   class="room-tag"
                                   style="background-color: #7f1d1d; color: #fff; text-decoration: none; cursor: pointer;"
                                   title="Click to create a purchase order for this item">
                                    Out of Stock
                                </a>
                            @else
                                <span class="room-tag" style="background-color: #7f1d1d; color: #fff;">Out of Stock</span>
                            @endif
                        @elseif($item->quantity_on_hand <= $item->reorder_level)
                            @if($isOwner)
                                <a href="{{ route('po.index', ['item' => $item->id]) }}"
                                   class="room-tag"
                                   style="background-color: #ef4444; color: #fff; text-decoration: none; cursor: pointer;"
                                   title="Click to create a purchase order for this item">
                                    Low Stock
                                </a>
                            @else
                                <span class="room-tag" style="background-color: #ef4444; color: #fff;">Low Stock</span>
                            @endif
                        @else
                            <span class="room-tag" style="background-color: #10b981; color: #fff;">In Stock</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="inventory-empty-row">
                    <td colspan="8">No inventory items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
function filterInventory() {
    const selected = document.getElementById('inventory-category-filter').value;
    const rows     = document.querySelectorAll('#inventory-table-body tr[data-category]');
    const emptyRow = document.getElementById('inventory-empty-row');
    let visible    = 0;

    rows.forEach(row => {
        if (selected === '' || row.dataset.category === selected) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    if (emptyRow) {
        emptyRow.style.display = visible === 0 ? '' : 'none';
    }
}
</script>
@endsection