@extends('layouts.app')

@section('page-title', 'Purchase Orders')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<!-- Create Purchase Order Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Create Purchase Order (PO)</h3>
    </div>
    <form action="{{ route('po.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Select Supplier</label>
                <select name="supplier_id" class="form-input select-dark" required>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Select Item to Restock</label>
                <select name="inventory_item_id" class="form-input select-dark" required>
                    @foreach($inventoryItems as $item)
                        <option value="{{ $item->id }}" {{ (int) request('item') === $item->id ? 'selected' : '' }}>
                            {{ $item->item_name }} ({{ $item->unit_of_measure }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Quantity Ordered</label>
                <input type="number" step="0.01" name="quantity_ordered" class="form-input" min="0.1" value="10.00" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Total PO Cost (₱)</label>
                <input type="number" step="0.01" name="total_cost" class="form-input" placeholder="2500.00" required />
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
            <tbody>
                @forelse($purchaseOrders as $po)
                <tr>
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
                <tr>
                    <td colspan="8">No purchase orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection