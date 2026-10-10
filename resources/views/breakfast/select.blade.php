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

@if(! $hasAnyItems)
    <div class="table-card">
        <div style="padding: 40px; text-align: center;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 48px; color: #ef4444; margin-bottom: 16px;"></i>
            <h3 style="margin-bottom: 12px;">No Breakfast Items Configured</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">
                No set meals or drinks are available. Please add items to the Menu Items page first.
            </p>
            <a href="{{ route('bookings.index') }}" class="btn-portal" style="padding: 10px 24px;">Back to Bookings</a>
        </div>
    </div>
@else
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
                        @foreach($setMeals as $meal)
                            <option value="{{ $meal->item_name }}">{{ $meal->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Rice</label>
                    <select name="persons[{{ $i }}][rice]" class="form-input select-dark" required>
                        <option value="plain">Plain Rice</option>
                        @foreach($riceItems as $rice)
                            <option value="{{ strtolower($rice->item_name) }}">{{ $rice->item_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Drink Type</label>
                    <select class="form-input select-dark" required
                        onchange="updateBreakfastDrinkList(this, '{{ $i }}')">
                        <option value="hot">Hot Drink</option>
                        <option value="cold">Cold Drink</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Drink Choice</label>
                    <select name="persons[{{ $i }}][drink_name]" id="bf_drink_{{ $i }}" class="form-input select-dark" required></select>
                    <input type="hidden" name="persons[{{ $i }}][drink_type]" id="bf_drink_type_{{ $i }}" value="hot" />
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
@endif
@endsection

@section('scripts')
<meta name="bf-hot-drinks" content="{{ $hotDrinks->pluck('item_name')->toJson() }}">
<meta name="bf-cold-drinks" content="{{ $coldDrinks->pluck('item_name')->toJson() }}">
<meta name="bf-paid-items" content="{{ $allPaidItems->map(fn($i) => ['name' => $i->item_name, 'price' => (float) $i->unit_price, 'category' => $i->category])->toJson() }}">
<script>
const HOT_DRINKS  = JSON.parse(document.querySelector('meta[name="bf-hot-drinks"]').content);
const COLD_DRINKS = JSON.parse(document.querySelector('meta[name="bf-cold-drinks"]').content);
const PAID_ITEMS  = JSON.parse(document.querySelector('meta[name="bf-paid-items"]').content);
const CATEGORIES  = [...new Set(PAID_ITEMS.map(i => i.category))];

function buildDrinkOpts(type) {
    return (type === 'hot' ? HOT_DRINKS : COLD_DRINKS)
        .map(d => `<option value="${d}">${d}</option>`).join('');
}

function updateBreakfastDrinkList(sel, idx) {
    document.getElementById(`bf_drink_${idx}`).innerHTML = buildDrinkOpts(sel.value);
    document.getElementById(`bf_drink_type_${idx}`).value = sel.value;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="bf_drink_"]').forEach(sel => {
        if (sel.id.indexOf('type_') === -1) {
            sel.innerHTML = buildDrinkOpts('hot');
        }
    });
});

// --- Extra Add-ons with chained Category + Item dropdowns ---
let bfExtraIdx = 0;

function addBreakfastExtra() {
    const container = document.getElementById('bf_extras_container');
    const idx = bfExtraIdx++;
    const div = document.createElement('div');
    div.id = `bf_extra_${idx}`;
    div.style.cssText = 'display:grid; grid-template-columns: 1.2fr 2fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:8px;';

    const categoryOpts = CATEGORIES.map(c => `<option value="${c}">${c}</option>`).join('');

    div.innerHTML = `
        <select id="bf_cat_${idx}" class="form-input select-dark" style="margin-bottom:0;" onchange="updateExtraItems(${idx})">
            <option value="">— None —</option>
            ${categoryOpts}
        </select>
        <select name="extra_items[${idx}][name]" id="bf_name_${idx}" class="form-input select-dark" style="margin-bottom:0;" onchange="updateExtraPrice(${idx})">
            <option value="">— None —</option>
        </select>
        <input type="hidden" name="extra_items[${idx}][price]" id="bf_ep_${idx}" value="0" />
        <input type="number" name="extra_items[${idx}][quantity]" class="form-input" min="1" value="1" style="margin-bottom:0;" onchange="calcAllTotals()" />
        <button type="button" onclick="removeBfExtra(${idx})" style="background:#ef4444;border:none;color:#fff;border-radius:6px;padding:6px 10px;cursor:pointer;">
            <i class="fa-solid fa-times"></i>
        </button>`;
    container.appendChild(div);
    calcAllTotals();
}

function updateExtraItems(idx) {
    const cat = document.getElementById(`bf_cat_${idx}`).value;
    const nameSel = document.getElementById(`bf_name_${idx}`);
    if (! cat) {
        nameSel.innerHTML = '<option value="">— None —</option>';
    } else {
        const items = PAID_ITEMS.filter(i => i.category === cat);
        nameSel.innerHTML = '<option value="">— None —</option>' +
            items.map(i => `<option value="${i.name}" data-price="${i.price}">${i.name} — ₱${i.price}</option>`).join('');
    }
    updateExtraPrice(idx);
}

function updateExtraPrice(idx) {
    const nameSel = document.getElementById(`bf_name_${idx}`);
    const opt = nameSel.options[nameSel.selectedIndex];
    const price = (opt && opt.dataset.price) ? opt.dataset.price : 0;
    document.getElementById(`bf_ep_${idx}`).value = price;
    calcAllTotals();
}

function removeBfExtra(idx) {
    const el = document.getElementById(`bf_extra_${idx}`);
    if (el) el.remove();
    calcAllTotals();
}

function calcAllTotals() {
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