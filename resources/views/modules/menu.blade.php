@extends('layouts.app')

@section('page-title', 'Menu Items')

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
                <input type="text" name="category" list="category-options" class="form-input"
                       placeholder="Pick existing or type new" required autocomplete="off" />
                <datalist id="category-options">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" class="form-input" placeholder="150.00" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Linked Raw Stock (Optional)</label>
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

<!-- Menu Catalog - Single Table + Category Filter -->
<div class="table-card">
    <div class="top-table">
        <h3>Menu Catalog</h3>
        <div style="display:flex; gap:10px; align-items:center;">
            <label style="font-size: 13px; color: var(--text-muted);">Filter:</label>
            <select id="category-filter" class="form-input select-dark" style="margin:0; padding: 6px 12px; min-width: 200px;" onchange="filterMenu()">
                <option value="">All Categories ({{ $menuItems->count() }})</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }} ({{ $menuItems->where('category', $cat)->count() }})</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dish / Drink Name</th>
                    <th>Category</th>
                    <th>Selling Price</th>
                    <th>Deduction per Order</th>
                </tr>
            </thead>
            <tbody id="menu-table-body">
                @forelse($menuItems as $item)
                <tr data-category="{{ $item->category }}">
                    <td>MNU-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $item->item_name }}</strong></td>
                    <td><span class="room-tag">{{ $item->category }}</span></td>
                    <td>₱ {{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->ingredient_qty_per_order }} {{ $item->inventoryItem->unit_of_measure ?? '' }}</td>
                </tr>
                @empty
                <tr id="empty-row">
                    <td colspan="5" style="text-align:center; padding: 24px; color: var(--text-muted);">
                        No menu items yet. Add your first offering above.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
function filterMenu() {
    const selected = document.getElementById('category-filter').value;
    const rows     = document.querySelectorAll('#menu-table-body tr[data-category]');
    const emptyRow = document.getElementById('empty-row');
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