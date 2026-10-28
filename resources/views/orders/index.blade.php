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

@if(in_array(Auth::user()->role, ['admin', 'operations']))
<!-- Order Form -->
<div class="table-card" style="margin-bottom: 30px;">
    <div class="top-table">
        <h3>Create New Guest Order</h3>
        <button type="button" onclick="addOrderLine()" class="btn-portal" style="padding: 6px 14px; font-size: 12px;">+ Add Item</button>
    </div>
    <form action="{{ route('orders.store') }}" method="POST" id="order-form">
        @csrf
        <div id="order-lines"></div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--accent-gold-border);">
            <div>
                <span style="font-size: 13px; color: var(--text-muted);">Running Total: </span>
                <strong id="order-total" style="font-size: 20px; color: var(--accent-gold-primary);">₱ 0.00</strong>
            </div>
            <button type="submit" class="btn-submit" style="padding: 10px 24px;">
                <i class="fa-solid fa-paper-plane"></i> Send to Kitchen
            </button>
        </div>
    </form>
</div>
@endif

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
                    <th class="text-left">Guest</th>
                    <th>Pax</th>
                    <th class="text-left">Meal Details</th>
                    <th class="text-left">Extras</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeBreakfasts as $bf)
                <tr>
                    <td>BK-{{ str_pad($bf->booking_id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left">
                        @if($bf->booking && $bf->booking->guest)
                            <strong>{{ $bf->booking->guest->first_name }} {{ $bf->booking->guest->last_name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $bf->booking->guest->contact_number }}</div>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $bf->pax }}</td>
                    <td class="text-left" style="font-size: 12px;">
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
                    <td class="text-left" style="font-size: 12px;">
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

<!-- Day Tour Orders -->
@if(in_array(Auth::user()->role, ['admin', 'operations']))
<div class="table-card" style="margin-bottom: 30px;">
    <div class="top-table">
        <h3><i class="fa-solid fa-ticket"></i> Pending Day Tour Tickets (Awaiting Drinks)</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th class="text-left">Guest / Group</th>
                    <th>Pax</th>
                    <th>Paid At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeDaytours as $dt)
                <tr>
                    <td><strong>Ticket #{{ $dt->ticket_no }}</strong></td>
                    <td class="text-left">{{ $dt->guest_name }}</td>
                    <td><span class="room-tag">{{ $dt->pax }} Pax</span></td>
                    <td>{{ $dt->created_at->format('h:i A') }}</td>
                    <td>
                        <a href="{{ route('daytour.redeem', $dt->id) }}" class="btn-submit" style="padding: 4px 12px; font-size: 12px; text-decoration: none;">
                            <i class="fa-solid fa-mug-hot"></i> Serve
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">No pending tickets. All guests have been served.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

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
                    <th class="text-left">Items Ordered</th>
                    <th class="text-right">Total Price</th>
                    <th>Status</th>
                    <th>Order Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeOrders as $order)
                <tr>
                    <td>ORD-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-left">
                        @foreach($order->orderItems as $item)
                            <div>{{ $item->quantity }}x {{ $item->menuItem->item_name ?? 'Item' }}</div>
                        @endforeach
                    </td>
                    <td class="text-right">₱ {{ number_format($order->total_price, 2) }}</td>
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

@section('scripts')
<meta name="menu-items-list" content="{{ $menuItems->map(fn($i) => [
    'id'         => $i->id,
    'name'       => $i->item_name,
    'price'      => (float) $i->unit_price,
    'category'   => $i->category,
    'available'  => $i->available_servings,
])->toJson() }}">
<script>
const MENU_ITEMS = JSON.parse(document.querySelector('meta[name="menu-items-list"]').content);
const MENU_CATEGORIES = [...new Set(MENU_ITEMS.map(i => i.category))];

let orderLineIdx = 0;

function buildItemOpts(category) {
    return MENU_ITEMS
        .filter(i => i.category === category)
        .map(i => {
            const priceStr = `₱${i.price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            let label, disabled = '';

            if (i.available === null) {
                label = `${i.name} — ${priceStr}`;
            } else if (i.available > 0) {
                label = `${i.name} — ${priceStr}  (${i.available} left)`;
            } else {
                label = `${i.name} — ${priceStr}  (out of stock)`;
                disabled = 'disabled';
            }

            return `<option value="${i.id}" data-price="${i.price}" data-available="${i.available ?? ''}" ${disabled}>${label}</option>`;
        })
        .join('');
}

function addOrderLine() {
    const container = document.getElementById('order-lines');
    const idx = orderLineIdx++;
    const div = document.createElement('div');
    div.id = `order_line_${idx}`;
    div.style.cssText = 'display: grid; grid-template-columns: 1.5fr 2.5fr 0.8fr 1fr auto; gap: 10px; align-items: center; margin-bottom: 8px;';

    const categoryOpts = MENU_CATEGORIES
        .map(c => `<option value="${c}">${c}</option>`).join('');

    div.innerHTML = `
        <select id="line_cat_${idx}" class="form-input select-dark" style="margin-bottom: 0;" onchange="updateLineItems(${idx})">
            ${categoryOpts}
        </select>
        <select name="items[${idx}][menu_item_id]" id="line_name_${idx}" class="form-input select-dark" style="margin-bottom: 0;" required onchange="updateLineSubtotal(${idx})"></select>
        <input type="number" name="items[${idx}][quantity]" id="line_qty_${idx}" class="form-input" min="1" value="1" required style="margin-bottom: 0;" onchange="updateLineSubtotal(${idx})" oninput="updateLineSubtotal(${idx})" />
        <span id="line_subtotal_${idx}" style="font-size: 13px; color: var(--accent-gold-primary); text-align: right; white-space: nowrap;">₱ 0.00</span>
        <button type="button" onclick="removeOrderLine(${idx})" style="background: #ef4444; border: none; color: #fff; border-radius: 6px; padding: 6px 10px; cursor: pointer;">
            <i class="fa-solid fa-times"></i>
        </button>`;

    container.appendChild(div);
    updateLineItems(idx);
}

function updateLineItems(idx) {
    const cat = document.getElementById(`line_cat_${idx}`).value;
    const nameSel = document.getElementById(`line_name_${idx}`);
    nameSel.innerHTML = buildItemOpts(cat);
    updateLineSubtotal(idx);
}

function updateLineSubtotal(idx) {
    const nameSel = document.getElementById(`line_name_${idx}`);
    const qtyEl   = document.getElementById(`line_qty_${idx}`);
    const opt = nameSel.options[nameSel.selectedIndex];
    const price = opt ? parseFloat(opt.dataset.price || 0) : 0;
    const qty   = parseInt(qtyEl.value || 0);
    const available = opt && opt.dataset.available !== '' ? parseInt(opt.dataset.available) : null;

    const qtyWrap = qtyEl.parentElement;
    const existingWarn = qtyWrap.querySelector('.stock-warn');
    if (existingWarn) existingWarn.remove();

    if (available !== null && qty > available) {
        const warn = document.createElement('span');
        warn.className = 'stock-warn';
        warn.style.cssText = 'color: #ef4444; font-size: 11px; margin-left: 4px; display: block;';
        warn.innerText = `Only ${available} available`;
        qtyWrap.appendChild(warn);
        qtyEl.style.borderColor = '#ef4444';
    } else {
        qtyEl.style.borderColor = '';
    }

    const sub = price * qty;
    document.getElementById(`line_subtotal_${idx}`).innerText =
        '₱ ' + sub.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    recalcOrderTotal();
}

function removeOrderLine(idx) {
    const el = document.getElementById(`order_line_${idx}`);
    if (el) el.remove();
    recalcOrderTotal();
}

function recalcOrderTotal() {
    let total = 0;
    document.querySelectorAll('[id^="order_line_"]').forEach(row => {
        const idx = row.id.replace('order_line_', '');
        const nameSel = document.getElementById(`line_name_${idx}`);
        const qtyEl   = document.getElementById(`line_qty_${idx}`);
        if (! nameSel || ! qtyEl) return;
        const opt = nameSel.options[nameSel.selectedIndex];
        const price = opt ? parseFloat(opt.dataset.price || 0) : 0;
        const qty   = parseInt(qtyEl.value || 0);
        total += price * qty;
    });
    document.getElementById('order-total').innerText =
        '₱ ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('order-lines')) {
        addOrderLine();
    }
});
document.getElementById('order-form').addEventListener('submit', function (e) {
    const issues = [];
    document.querySelectorAll('[id^="order_line_"]').forEach(row => {
        const idx = row.id.replace('order_line_', '');
        const nameSel = document.getElementById(`line_name_${idx}`);
        const qtyEl   = document.getElementById(`line_qty_${idx}`);
        if (! nameSel || ! qtyEl) return;

        const opt = nameSel.options[nameSel.selectedIndex];
        if (! opt) return;
        const available = opt.dataset.available !== '' ? parseInt(opt.dataset.available) : null;
        const qty = parseInt(qtyEl.value || 0);

        if (available !== null && qty > available) {
            issues.push(`${opt.text.split(' — ')[0].trim()}: only ${available} available, requested ${qty}`);
        }
    });

    if (issues.length > 0) {
        e.preventDefault();
        alert('Insufficient stock:\n\n' + issues.join('\n') + '\n\nPlease adjust the quantities and try again.');
    }
});
</script>
@endsection