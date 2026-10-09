@extends('layouts.app')

@section('page-title', 'Food & Beverage Orders')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if($errors->has('stock_error'))
    <div class="auth-alert error" style="margin-bottom: 20px;">
        {{ $errors->first('stock_error') }}
    </div>
@endif

<!-- Order Form -->
<div class="table-card" style="margin-bottom: 30px;">
    <div class="top-table">
        <h3>Create New Guest Order</h3>
    </div>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Select Menu Item</label>
                <select name="items[0][menu_item_id]" class="form-input select-dark" required>
                    @php
                        $grouped = $menuItems->groupBy('category');
                    @endphp
                    @foreach($grouped as $category => $items)
                        <optgroup label="{{ $category }}">
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->item_name }} (₱ {{ number_format($item->unit_price, 2) }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Quantity</label>
                <input type="number" name="items[0][quantity]" class="form-input" min="1" value="1" required />
            </div>
            <div>
                <button type="submit" class="btn-submit" style="padding: 10px;">Send to Kitchen</button>
            </div>
        </div>
    </form>
</div>

<!-- Active Orders Table -->
<div class="table-card">
    <div class="top-table">
        <h3>Active Food Orders</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Items Ordered</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Order Time</th>
                    <th>Settlement</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeOrders as $order)
                <tr>
                    <td>ORD-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        @foreach($order->orderItems as $item)
                            <div>{{ $item->quantity }}x {{ $item->menuItem->item_name ?? 'Item' }}</div>
                        @endforeach
                    </td>
                    <td>₱ {{ number_format($order->total_price, 2) }}</td>
                    <td><span class="room-tag">{{ ucfirst($order->order_status) }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M d, h:i A') }}</td>
                    <td>
                        @if($order->order_status !== 'paid')
                        <form action="{{ route('orders.payment', $order->id) }}" method="POST" style="display:flex; gap:6px; justify-content:center;">
                            @csrf
                            <select name="payment_method" required style="padding: 4px; border-radius: 4px;">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank</option>
                            </select>
                            <input type="text" name="manual_receipt_no" placeholder="Receipt #" required style="width: 80px; padding: 4px; border-radius: 4px;" />
                            <button type="submit" class="btn-portal" style="padding: 4px 8px;">Pay</button>
                        </form>
                        @else
                        <span>Paid</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">No active food orders in queue.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection