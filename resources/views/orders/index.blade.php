@extends('layouts.app')

@section('page-title', 'Food & Beverage Orders')

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
                    @php $grouped = $menuItems->groupBy('category'); @endphp
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

<!-- Booked Breakfasts -->
<div class="table-card" style="margin-bottom: 30px;">
    <div class="top-table">
        <h3><i class="fa-solid fa-egg"></i> Booked Breakfasts (from Reservations)</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Pax</th>
                    <th>Meal Details</th>
                    <th>Extras</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeBreakfasts as $bf)
                <tr>
                    <td>BK-{{ str_pad($bf->booking_id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        @if($bf->booking && $bf->booking->guest)
                            <strong>{{ $bf->booking->guest->first_name }} {{ $bf->booking->guest->last_name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $bf->booking->guest->contact_number }}</div>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $bf->pax }}</td>
                    <td style="text-align:left; font-size: 12px;">
                        @php $details = $bf->order_details ?? ['persons' => [], 'extras' => []]; @endphp
                        @foreach(($details['persons'] ?? []) as $i => $p)
                            <div style="margin-bottom: 4px;">
                                <strong>P{{ $i + 1 }}:</strong>
                                {{ $p['main_dish'] ?? '—' }},
                                {{ ucfirst($p['rice'] ?? 'plain') }} rice,
                                {{ $p['drink_name'] ?? '—' }}
                            </div>
                        @endforeach
                    </td>
                    <td style="text-align:left; font-size: 12px;">
                        @if(! empty($details['extras']))
                            @foreach($details['extras'] as $ex)
                                <div>{{ $ex['quantity'] }}x {{ $ex['name'] }}
                                    <span style="color: var(--text-muted);">— ₱{{ number_format($ex['subtotal'] ?? 0, 2) }}</span>
                                </div>
                            @endforeach
                            <div style="margin-top: 4px; color: var(--accent-gold-primary); font-weight: 600;">
                                Total: ₱{{ number_format($bf->extra_amount, 2) }}
                            </div>
                        @else
                            <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="room-tag">{{ ucfirst($bf->status) }}</span>
                    </td>
                    <td>
                        @if($bf->status === 'pending')
                            <form action="{{ route('breakfast.status', $bf->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="_target_status" value="preparing">
                                <button type="submit" name="new_status" value="preparing" class="btn-portal" style="padding: 4px 10px; font-size: 11px;">
                                    Mark Preparing
                                </button>
                            </form>
                        @elseif($bf->status === 'preparing')
                            <form action="{{ route('breakfast.status', $bf->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" name="new_status" value="served" class="btn-portal" style="padding: 4px 10px; font-size: 11px;">
                                    Mark Served
                                </button>
                            </form>
                        @else
                            <span style="color: #10b981; font-size: 12px; font-weight: 600;">
                                <i class="fa-solid fa-check-circle"></i> Served
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">No pending breakfast orders.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Active Walk-in Orders -->
<div class="table-card">
    <div class="top-table">
        <h3>Active Walk-in Orders</h3>
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
                    <th>Actions</th>
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
                        <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                            @if($order->order_status === 'pending')
                            <form action="{{ route('orders.status', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" name="new_status" value="preparing" class="btn-portal" style="padding: 4px 10px; font-size: 11px;">
                                    Preparing
                                </button>
                            </form>
                            @elseif($order->order_status === 'preparing')
                            <form action="{{ route('orders.status', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" name="new_status" value="served" class="btn-portal" style="padding: 4px 10px; font-size: 11px;">
                                    Served
                                </button>
                            </form>
                            @elseif($order->order_status === 'served')
                            <form action="{{ route('orders.payment', $order->id) }}" method="POST" style="display:flex; gap:4px;">
                                @csrf
                                <select name="payment_method" required style="padding: 4px; border-radius: 4px; font-size: 11px;">
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
                                    <option value="bank_transfer">Bank</option>
                                </select>
                                <input type="text" name="manual_receipt_no" placeholder="Receipt #" required style="width: 80px; padding: 4px; border-radius: 4px; font-size: 11px;" />
                                <button type="submit" class="btn-portal" style="padding: 4px 8px; font-size: 11px;">Pay</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">No active walk-in orders in queue.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection