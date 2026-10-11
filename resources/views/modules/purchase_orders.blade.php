@extends('layouts.app')

@section('page-title', 'Purchase Orders')

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

<!-- Create Purchase Order Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Create Purchase Order (PO)</h3>
    </div>
    <form action="{{ route('po.store') }}" method="POST" id="po-form">
        @csrf
        <div style="display: grid; grid-template-columns: 1.5fr 2fr 1fr 1fr 1.5fr; gap: 14px; align-items: end;">

            <div class="form-group" style="margin-bottom: 0;">
                <label>Select Supplier</label>
                <select name="supplier_id" id="po-supplier" class="form-input select-dark" required>
                    <option value="">-- Pick a supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ (int) request('supplier') === $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label>Item to Restock</label>
                <select name="inventory_item_id" id="po-item" class="form-input select-dark" required>
                    <option value="">-- Pick a supplier first --</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label>Quantity</label>
                <input type="number" step="0.01" name="quantity_ordered" id="po-qty" class="form-input" min="0.1" value="1" required oninput="calcPOTotal()" />
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label>Unit Price (₱)</label>
                <input type="number" step="0.01" name="unit_price" id="po-price" class="form-input" min="0" value="0.00" required oninput="calcPOTotal()" />
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label>Total Cost (₱)</label>
                <input type="text" id="po-total" class="form-input" readonly value="0.00" style="background: rgba(0,0,0,0.4); cursor: not-allowed;" />
            </div>
        </div>
        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Issue Purchase Order</button>
        </div>
    </form>
</div>

<!-- Purchase Orders Table -->
<div class="table-card">
    <div class="top-table">
        <h3>Procurement & Delivery Receipts</h3>
        <div style="display:flex; gap:10px; align-items:center;">
            <label style="font-size: 13px; color: var(--text-muted);">Filter:</label>
            <select id="po-supplier-filter" class="form-input select-dark" style="margin:0; padding: 6px 12px; min-width: 260px;" onchange="filterPOs()">
                <option value="">All Suppliers ({{ $purchaseOrders->count() }})</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">
                        {{ $supplier->supplier_name }} ({{ $purchaseOrders->where('supplier_id', $supplier->id)->count() }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>PO #</th>
                    <th>Supplier</th>
                    <th>Item Ordered</th>
                    <th>Quantity</th>
                    <th>Total Cost</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="po-table-body">
                @forelse($purchaseOrders as $po)
                <tr data-supplier="{{ $po->supplier_id }}">
                    <td>PO-{{ str_pad($po->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $po->supplier->supplier_name ?? 'N/A' }}</td>
                    <td>{{ $po->inventoryItem->item_name ?? 'N/A' }}</td>
                    <td>{{ number_format($po->quantity_ordered, 2) }} {{ $po->inventoryItem->unit_of_measure ?? '' }}</td>
                    <td>₱ {{ number_format($po->total_cost, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($po->order_date)->format('M d, Y') }}</td>
                    <td><span class="room-tag">{{ ucfirst($po->purchase_order_status) }}</span></td>
                    <td>
                        @if($po->purchase_order_status === 'pending')
                        <form action="{{ route('po.receive', $po->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-portal" style="padding: 4px 10px;">Receive Stock</button>
                        </form>
                        @else
                        <span>Stock Added</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="po-empty-row">
                    <td colspan="8">No purchase orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<meta name="po-items-map" content="{{ $inventoryItems->map(fn($i) => ['id' => $i->id, 'name' => $i->item_name, 'unit' => $i->unit_of_measure, 'supplier_id' => $i->preferred_supplier_id])->toJson() }}">
<script>
    const PO_ITEMS = JSON.parse(document.querySelector('meta[name="po-items-map"]').content);

    function updatePOItemList() {
        const supplierId = document.getElementById('po-supplier').value;
        const itemSelect = document.getElementById('po-item');
        const currentItemId = itemSelect.value;

        if (! supplierId) {
            itemSelect.innerHTML = '<option value="">-- Pick a supplier first --</option>';
            return;
        }

        const filtered = PO_ITEMS.filter(i => String(i.supplier_id) === String(supplierId));

        if (filtered.length === 0) {
            itemSelect.innerHTML = '<option value="">-- No items for this supplier --</option>';
            return;
        }

        itemSelect.innerHTML = '<option value="">-- Pick an item --</option>' +
            filtered.map(i => `<option value="${i.id}" ${String(i.id) === currentItemId ? 'selected' : ''}>${i.name} (${i.unit})</option>`).join('');
    }

    function calcPOTotal() {
        const qty   = parseFloat(document.getElementById('po-qty').value || 0);
        const price = parseFloat(document.getElementById('po-price').value || 0);
        const total = qty * price;
        document.getElementById('po-total').value = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function filterPOs() {
        const selected = document.getElementById('po-supplier-filter').value;
        const rows     = document.querySelectorAll('#po-table-body tr[data-supplier]');
        const emptyRow = document.getElementById('po-empty-row');
        let visible    = 0;

        rows.forEach(row => {
            if (selected === '' || row.dataset.supplier === selected) {
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

    document.getElementById('po-supplier').addEventListener('change', updatePOItemList);

    document.addEventListener('DOMContentLoaded', function () {
        updatePOItemList();

        const urlParams = new URLSearchParams(window.location.search);
        const preselectItem = urlParams.get('item');
        if (preselectItem) {
            const itemSelect = document.getElementById('po-item');
            setTimeout(function () {
                itemSelect.value = preselectItem;
            }, 50);
        }
    });
</script>
@endsection