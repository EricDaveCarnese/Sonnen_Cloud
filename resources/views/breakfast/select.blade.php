@extends('layouts.app')

@section('page-title', 'Breakfast Selection')

@section('content')
@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
@endif

<!-- Booking Summary -->
<div class="table-card" style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h3 style="margin-bottom:4px;"><i class="fa-solid fa-utensils"></i> Breakfast & Balance Payment</h3>
            <div style="font-size:13px; color:#555;">
                <strong>BK-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</strong> —
                {{ $booking->guest->first_name }} {{ $booking->guest->last_name }}
                ({{ $booking->guest->contact_number }})
            </div>
        </div>
        <div style="text-align:right; font-size:13px; color:#555;">
            <div>Check-in: <strong>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }} 1:00 PM</strong></div>
            <div>Check-out: <strong>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }} 12:00 NN</strong></div>
            <div style="margin-top:4px;">Remaining Balance:
                <strong style="color:#ef4444; font-size:16px;">₱ {{ number_format($booking->remaining_balance, 2) }}</strong>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('breakfast.submit', $booking->id) }}" method="POST">
    @csrf

    <!-- Balance Payment -->
    <div class="table-card" style="margin-bottom: 20px;">
        <div class="top-table"><h3>Balance Payment Details</h3></div>
        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:14px;">
            <div class="form-group" style="margin-bottom:0;">
                <label>Amount Paid (₱)</label>
                <input type="number" name="amount_paid" step="0.01" class="form-input"
                    value="{{ $booking->remaining_balance }}" min="1" required />
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Payment Method</label>
                <select name="payment_method" class="form-input select-dark" required>
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Receipt Number</label>
                <input type="text" name="manual_receipt_no" class="form-input" placeholder="OR-001" required />
            </div>
        </div>
    </div>

    <!-- Per-Person Breakfast Selection -->
    <div class="table-card" style="margin-bottom: 20px;">
        <div class="top-table">
            <h3><i class="fa-solid fa-egg"></i> Set Meal Selection (Included in Package)</h3>
            <span style="font-size:12px; color:var(--accent-gold-primary);">1 Set Meal + Rice + 1 Drink per person — FREE</span>
        </div>
        @php $pax = $booking->pax ?? 2; @endphp
        @for($i = 0; $i < $pax; $i++)
        <div style="background:rgba(46,34,24,0.6); border:1px solid var(--accent-gold-border); border-radius:10px; padding:14px; margin-bottom:12px;">
            <h4 style="color:var(--accent-gold-primary); font-size:14px; margin-bottom:10px;">Person {{ $i + 1 }}</h4>
            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr 2fr; gap:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Set Meal</label>
                    <select name="persons[{{ $i }}][main_dish]" class="form-input select-dark" required>
                        <option value="Tapsilog (Beef)">Tapsilog (Beef)</option>
                        <option value="Tapsilog (Pork)">Tapsilog (Pork)</option>
                        <option value="Porksilog">Porksilog</option>
                        <option value="Chickensilog">Chickensilog</option>
                        <option value="Longsilog">Longsilog</option>
                        <option value="Bangsilog">Bangsilog</option>
                        <option value="Cornsilog">Cornsilog</option>
                        <option value="Hotsilog">Hotsilog</option>
                        <option value="Sisilog (Chicken)">Sisilog (Chicken)</option>
                        <option value="Sisilog (Pork)">Sisilog (Pork)</option>
                        <option value="Tosilog">Tosilog</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Rice</label>
                    <select name="persons[{{ $i }}][rice]" class="form-input select-dark" required>
                        <option value="plain">Plain Rice</option>
                        <option value="fried">Fried Rice</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Drink Type</label>
                    <select name="persons[{{ $i }}][drink_type]" class="form-input select-dark" required
                    onchange="updateBreakfastDrinkList(this, '{{ $i }}')">
                        <option value="hot">Hot Drink</option>
                        <option value="cold">Cold Drink</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Drink Choice</label>
                    <select name="persons[{{ $i }}][drink_name]" id="bf_drink_{{ $i }}" class="form-input select-dark" required></select>
                </div>
            </div>
        </div>
        @endfor
    </div>

    <!-- Extra Add-ons -->
    <div class="table-card" style="margin-bottom: 20px;">
        <div class="top-table">
            <h3><i class="fa-solid fa-plus-circle"></i> Extra Add-ons (Paid Items)</h3>
            <button type="button" onclick="addBreakfastExtra()" class="btn-portal" style="padding:6px 14px; font-size:12px;">+ Add Item</button>
        </div>

        <!-- Quick Add -->
        <div style="margin-bottom:14px; display:flex; flex-wrap:wrap; gap:8px;">
            <span style="font-size:12px; color:var(--text-muted); align-self:center;">Quick Add:</span>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Pancit Canton', 25, 'Kalamansi')">Pancit Canton ₱25</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Extra Big Pancit Canton', 35, 'Kalamansi')">Big Pancit ₱35</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Plain Rice', 15, '')">Plain Rice ₱15</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Fried Rice', 20, '')">Fried Rice ₱20</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Suman (Malagkit)', 10, '')">Suman ₱10</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Hot Drink', 25, '')">Hot Drink ₱25</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Nagaraya', 20, '')">Nagaraya ₱20</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Piattos', 25, '')">Piattos ₱25</button>
            <button type="button" class="btn-portal" style="padding:4px 10px; font-size:11px;" onclick="quickAddExtra('Nova', 25, '')">Nova ₱25</button>
        </div>

        <div id="bf_extras_container"></div>
        <div style="text-align:right; margin-top:10px; font-size:14px; color:var(--accent-gold-primary);">
            Extras Total: <strong id="bf_extras_total">₱ 0.00</strong>
        </div>
    </div>

    <div style="text-align:right;">
        <a href="{{ route('bookings.index') }}" class="btn-portal" style="margin-right:12px;">Cancel</a>
        <button type="submit" class="btn-submit" style="width:auto; padding:12px 32px; font-size:15px;">
            <i class="fa-solid fa-check-circle"></i> Save Breakfast Order & Record Balance Payment
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
const HOT_DRINKS = [
    'Sikwate',
    'Kopiko Brown','Kopiko Black','Kopiko Blanca','Kopiko L.A. Coffee',
    'Kopiko Cappuccino','Kopiko Café Mocha','Kopiko Double Cups',
    'Nescafe Original','Nescafe Creamy White','Nescafe Creamy Latte',
    'Nescafe Sugarfree Original','Nescafe Sugarfree Creamy White',
    'Bearbrand Swak','Bearbrand Adultplus','Birch Tree',
    'Bearbrand Chocolate','Birch Tree Chocolate','Milo'
];

const COLD_DRINKS = [
    'Orange Juice','Pineapple Juice','Mango Juice',
    'Apple Iced Tea','Lemon Iced Tea','Peach Iced Tea',
    'Nature Spring 250ml'
];

const EXTRA_MENU = [
    // Food add-ons
    { name: 'Pancit Canton',              price: 25 },
    { name: 'Extra Big Pancit Canton',    price: 35 },
    { name: 'Plain Rice',                 price: 15 },
    { name: 'Fried Rice',                 price: 20 },
    { name: 'Suman (Malagkit)',           price: 10 },
    // Snacks
    { name: 'Nagaraya',                   price: 20 },
    { name: 'Fishda',                     price: 20 },
    { name: 'Cheezy',                     price: 20 },
    { name: 'Mangjuan',                   price: 20 },
    { name: 'Cracklings',                 price: 20 },
    { name: 'Clover',                     price: 20 },
    { name: 'Piattos',                    price: 25 },
    { name: 'Nova',                       price: 25 },
    // Paid drinks - hot
    { name: 'Sikwate (Extra)',            price: 25 },
    { name: 'Kopiko Brown (Extra)',       price: 25 },
    { name: 'Kopiko Black (Extra)',       price: 25 },
    { name: 'Kopiko Blanca (Extra)',      price: 25 },
    { name: 'Kopiko L.A. Coffee (Extra)', price: 25 },
    { name: 'Kopiko Cappuccino (Extra)',  price: 25 },
    { name: 'Kopiko Café Mocha (Extra)',  price: 25 },
    { name: 'Kopiko Double Cups (Extra)', price: 25 },
    { name: 'Nescafe Original (Extra)',   price: 25 },
    { name: 'Nescafe Creamy White (Extra)', price: 25 },
    { name: 'Nescafe Creamy Latte (Extra)', price: 25 },
    { name: 'Nescafe Sugarfree Original (Extra)', price: 25 },
    { name: 'Nescafe Sugarfree Creamy White (Extra)', price: 25 },
    { name: 'Bearbrand Swak (Extra)',     price: 25 },
    { name: 'Bearbrand Adultplus (Extra)', price: 25 },
    { name: 'Birch Tree (Extra)',         price: 25 },
    { name: 'Bearbrand Chocolate (Extra)', price: 25 },
    { name: 'Birch Tree Chocolate (Extra)', price: 25 },
    { name: 'Milo (Extra)',               price: 25 },
    // Paid drinks - cold
    { name: 'Orange Juice (Extra)',       price: 20 },
    { name: 'Pineapple Juice (Extra)',    price: 20 },
    { name: 'Mango Juice (Extra)',        price: 20 },
    { name: 'Apple Iced Tea (Extra)',     price: 20 },
    { name: 'Lemon Iced Tea (Extra)',     price: 20 },
    { name: 'Peach Iced Tea (Extra)',     price: 20 },
    { name: 'Nature Spring 250ml (Extra)', price: 20 },
    { name: 'Nature Spring 500ml',        price: 25 },
    { name: 'Nature Spring 1000ml',       price: 35 },
    { name: 'Nature Spring 1.5L',         price: 45 },
];

function buildDrinkOpts(type) {
    return (type === 'hot' ? HOT_DRINKS : COLD_DRINKS)
        .map(d => `<option value="${d}">${d}</option>`).join('');
}

function updateBreakfastDrinkList(sel, idx) {
    document.getElementById(`bf_drink_${idx}`).innerHTML = buildDrinkOpts(sel.value);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="bf_drink_"]').forEach(sel => {
        sel.innerHTML = buildDrinkOpts('hot');
    });
});

let bfExtraIdx = 0;
function addBreakfastExtra(name = '', price = 0, variant = '') {
    const container = document.getElementById('bf_extras_container');
    const idx = bfExtraIdx++;
    const div = document.createElement('div');
    div.id = `bf_extra_${idx}`;
    div.style.cssText = 'display:grid; grid-template-columns: 2fr 1.5fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:8px;';

    const opts = EXTRA_MENU.map(item =>
        `<option value="${item.name}" data-price="${item.price}" ${item.name === name ? 'selected' : ''}>${item.name} — ₱${item.price}</option>`
    ).join('');

    div.innerHTML = `
        <select name="extra_items[${idx}][name]" class="form-input select-dark" style="margin-bottom:0;" onchange="updateExtraVariant(this,${idx})">
            ${opts}
        </select>
        <input type="text" name="extra_items[${idx}][variant]" id="bf_ev_${idx}" class="form-input" placeholder="Variant (optional)" value="${variant}" style="margin-bottom:0;" />
        <input type="hidden" name="extra_items[${idx}][price]" id="bf_ep_${idx}" value="${price || 25}" />
        <input type="number" name="extra_items[${idx}][quantity]" class="form-input" min="1" value="1" style="margin-bottom:0;" onchange="calcExtrasTotal()" />
        <button type="button" onclick="removeBfExtra(${idx})" style="background:#ef4444;border:none;color:#fff;border-radius:6px;padding:6px 10px;cursor:pointer;">
            <i class="fa-solid fa-times"></i>
        </button>`;
    container.appendChild(div);

    const opt = div.querySelector('select').options[div.querySelector('select').selectedIndex];
    document.getElementById(`bf_ep_${idx}`).value = opt.dataset.price || price || 25;
    calcExtrasTotal();
}

function quickAddExtra(name, price, variant) { addBreakfastExtra(name, price, variant); }

function updateExtraVariant(sel, idx) {
    document.getElementById(`bf_ep_${idx}`).value = sel.options[sel.selectedIndex].dataset.price || 0;
    calcExtrasTotal();
}

function removeBfExtra(idx) {
    const el = document.getElementById(`bf_extra_${idx}`);
    if (el) el.remove();
    calcExtrasTotal();
}

function calcExtrasTotal() {
    let total = 0;
    document.querySelectorAll('[id^="bf_extra_"]').forEach(row => {
        const price = parseFloat(row.querySelector('input[type="hidden"]')?.value || 0);
        const qty   = parseInt(row.querySelector('input[type="number"]')?.value || 0);
        total += price * qty;
    });
    document.getElementById('bf_extras_total').innerText =
        '₱ ' + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}
</script>
@endsection