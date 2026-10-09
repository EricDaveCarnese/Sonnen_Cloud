@extends('layouts.app')

@section('page-title', 'Day Tour')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
@endif

<!-- Day Tour Registration Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3><i class="fa-solid fa-sun"></i> Register Day Tour Guests</h3>
        <span style="font-size: 13px; color: var(--accent-gold-primary);">
            <i class="fa-solid fa-ticket"></i> ₱100/person — Includes 1 Drink + 1 Suman (Malagkit)
        </span>
    </div>
    <form action="{{ route('daytour.store') }}" method="POST" id="daytourForm">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1.5fr 0.5fr 1fr 1.5fr; gap: 14px; margin-bottom: 14px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Guest / Group Name</label>
                <input type="text" name="guest_name" class="form-input" placeholder="e.g. Santos Family" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Contact Number</label>
                <input type="text" name="contact_number" class="form-input" placeholder="0917xxxxxxx" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Pax</label>
                <input type="number" name="pax" id="daytour_pax" class="form-input" min="1" max="50" value="1" required onchange="generateDaytourInclusions()" />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Payment Method</label>
                <select name="payment_method" class="form-input select-dark" required>
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Receipt Number</label>
                <input type="text" name="manual_receipt_no" class="form-input" placeholder="OR-001" required />
            </div>
        </div>

        <!-- Drink Selections per Person -->
        <div style="background: rgba(46,34,24,0.7); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 16px; margin-bottom: 14px;">
            <h4 style="color: var(--accent-gold-primary); margin-bottom: 12px; font-size: 14px;">
                <i class="fa-solid fa-mug-hot"></i> Drink Inclusions per Person (1 FREE per person)
            </h4>
            <div id="daytour_inclusions"></div>
        </div>

        <!-- Extra Add-ons -->
        <div style="background: rgba(46,34,24,0.5); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 16px; margin-bottom: 14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h4 style="color: var(--accent-gold-primary); font-size: 14px; margin:0;">
                    <i class="fa-solid fa-plus-circle"></i> Extra Orders (Paid)
                </h4>
                <button type="button" onclick="addDaytourExtra()" class="btn-portal" style="padding: 4px 12px; font-size: 12px;">+ Add Item</button>
            </div>
            <div id="daytour_extras"></div>
        </div>

        <!-- Price Summary -->
        <div style="background: rgba(46,34,24,0.7); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 14px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom: 14px;">
            <div>
                <span style="color: var(--text-muted); font-size: 13px;">Pax: </span>
                <strong id="dt_summary_pax" style="color: #fff;">1</strong>
                <span style="color: var(--text-muted); font-size: 13px; margin-left: 16px;">Entrance: </span>
                <strong id="dt_summary_entrance" style="color: var(--accent-gold-primary);">₱ 100.00</strong>
                <span style="color: var(--text-muted); font-size: 13px; margin-left: 16px;">Extras: </span>
                <strong id="dt_summary_extras" style="color: #fff;">₱ 0.00</strong>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 13px;">Total to Collect: </span>
                <strong id="dt_summary_total" style="font-size: 22px; color: var(--accent-gold-primary);">₱ 100.00</strong>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 28px;">
                <i class="fa-solid fa-check-circle"></i> Confirm Day Tour & Collect Payment
            </button>
        </div>
    </form>
</div>

<!-- Day Tour Records Table -->
<div class="table-card">
    <div class="top-table">
        <h3>Day Tour Records</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>DT #</th>
                    <th>Guest / Group</th>
                    <th>Contact</th>
                    <th>Pax</th>
                    <th>Entrance Fee</th>
                    <th>Extras</th>
                    <th>Total Collected</th>
                    <th>Method</th>
                    <th>Receipt #</th>
                    <th>Recorded By</th>
                    <th>Visit Date</th>
                    <th>Inclusions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daytours as $dt)
                <tr>
                    <td>DT-{{ str_pad($dt->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $dt->guest_name }}</strong></td>
                    <td>{{ $dt->contact_number }}</td>
                    <td>{{ $dt->pax }}</td>
                    <td>₱ {{ number_format($dt->entrance_fee, 2) }}</td>
                    <td>₱ {{ number_format($dt->extra_amount, 2) }}</td>
                    <td><strong>₱ {{ number_format($dt->total_amount, 2) }}</strong></td>
                    <td><span class="room-tag">{{ strtoupper($dt->payment_method) }}</span></td>
                    <td>{{ $dt->manual_receipt_no }}</td>
                    <td>{{ $dt->user->fullname ?? 'System' }}</td>
                    <td>{{ \Carbon\Carbon::parse($dt->visit_date)->format('M d, Y h:i A') }}</td>
                    <td style="text-align:left; font-size:12px;">
                        @foreach($dt->inclusions as $i => $inc)
                            <div>P{{ $i+1 }}: {{ ucfirst($inc['drink_type']) }} — {{ $inc['drink_name'] }} + Suman</div>
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr><td colspan="12">No day tour records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
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
    'Nature Spring 250ml','Nature Spring 500ml',
    'Nature Spring 1000ml','Nature Spring 1.5L'
];

const EXTRA_ITEMS = [
    { name: 'Pancit Canton', price: 25 },
    { name: 'Extra Big Pancit Canton', price: 35 },
    { name: 'Plain Rice', price: 15 },
    { name: 'Fried Rice', price: 20 },
    { name: 'Suman (Malagkit)', price: 10 },
    { name: 'Nagaraya', price: 20 },
    { name: 'Fishda', price: 20 },
    { name: 'Cheezy', price: 20 },
    { name: 'Mangjuan', price: 20 },
    { name: 'Cracklings', price: 20 },
    { name: 'Clover', price: 20 },
    { name: 'Piattos', price: 25 },
    { name: 'Nova', price: 25 },
    { name: 'Hot Drink', price: 25 },
    { name: 'Cold Drink (Juice/Iced Tea)', price: 20 },
];

function buildDrinkOptions(type) {
    return (type === 'hot' ? HOT_DRINKS : COLD_DRINKS)
        .map(d => `<option value="${d}">${d}</option>`).join('');
}

function generateDaytourInclusions() {
    const pax = parseInt(document.getElementById('daytour_pax').value) || 1;
    const container = document.getElementById('daytour_inclusions');
    container.innerHTML = '';
    for (let i = 0; i < pax; i++) {
        const div = document.createElement('div');
        div.style.cssText = 'display:grid; grid-template-columns: auto 1fr 2fr; gap:10px; align-items:center; margin-bottom:8px; padding:8px; background:rgba(0,0,0,0.2); border-radius:6px;';
        div.innerHTML = `
            <span style="font-size:13px; color:var(--accent-gold-primary); font-weight:600; white-space:nowrap;">Person ${i+1}</span>
            <select name="inclusions[${i}][drink_type]" class="form-input select-dark" style="margin-bottom:0;" onchange="updateDrinkList(this,${i})">
                <option value="hot">Hot Drink</option>
                <option value="cold">Cold Drink</option>
            </select>
            <select name="inclusions[${i}][drink_name]" id="drink_name_${i}" class="form-input select-dark" style="margin-bottom:0;">
                ${buildDrinkOptions('hot')}
            </select>`;
        container.appendChild(div);

        const note = document.createElement('div');
        note.style.cssText = 'font-size:11px; color:var(--text-muted); margin-bottom:10px; padding-left:8px;';
        note.innerHTML = `<i class="fa-solid fa-check" style="color:#10b981;"></i> 1 Suman (Malagkit) automatically included`;
        container.appendChild(note);
    }
    updateDaytourTotal();
}

function updateDrinkList(sel, index) {
    document.getElementById(`drink_name_${index}`).innerHTML = buildDrinkOptions(sel.value);
}

let daytourExtraCount = 0;
function addDaytourExtra() {
    const container = document.getElementById('daytour_extras');
    const idx = daytourExtraCount++;
    const div = document.createElement('div');
    div.id = `dt_extra_${idx}`;
    div.style.cssText = 'display:grid; grid-template-columns: 2fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:8px;';

    const opts = EXTRA_ITEMS.map(item =>
        `<option value="${item.name}" data-price="${item.price}">${item.name} — ₱${item.price}</option>`
    ).join('');

    div.innerHTML = `
        <select name="extra_orders[${idx}][name]" class="form-input select-dark" style="margin-bottom:0;" onchange="syncExtraPrice(this,${idx})">
            ${opts}
        </select>
        <input type="hidden" name="extra_orders[${idx}][price]" id="dt_ep_${idx}" value="25" />
        <input type="number" name="extra_orders[${idx}][quantity]" class="form-input" min="1" value="1" style="margin-bottom:0;" onchange="updateDaytourTotal()" />
        <button type="button" onclick="removeDtExtra(${idx})" style="background:#ef4444;border:none;color:#fff;border-radius:6px;padding:6px 10px;cursor:pointer;">
            <i class="fa-solid fa-times"></i>
        </button>`;
    container.appendChild(div);

    const firstOpt = div.querySelector('select').options[0];
    document.getElementById(`dt_ep_${idx}`).value = firstOpt ? firstOpt.dataset.price : 25;
    updateDaytourTotal();
}

function syncExtraPrice(sel, idx) {
    document.getElementById(`dt_ep_${idx}`).value = sel.options[sel.selectedIndex].dataset.price || 0;
    updateDaytourTotal();
}

function removeDtExtra(idx) {
    const el = document.getElementById(`dt_extra_${idx}`);
    if (el) el.remove();
    updateDaytourTotal();
}

function updateDaytourTotal() {
    const pax = parseInt(document.getElementById('daytour_pax').value) || 1;
    const entranceTotal = pax * 100;
    let extras = 0;

    document.querySelectorAll('[id^="dt_extra_"]').forEach(row => {
        const price = parseFloat(row.querySelector('input[type="hidden"]')?.value || 0);
        const qty   = parseInt(row.querySelector('input[type="number"]')?.value || 0);
        extras += price * qty;
    });

    const total = entranceTotal + extras;
    const fmt = n => '₱ ' + n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('dt_summary_pax').innerText      = pax;
    document.getElementById('dt_summary_entrance').innerText = fmt(entranceTotal);
    document.getElementById('dt_summary_extras').innerText   = fmt(extras);
    document.getElementById('dt_summary_total').innerText    = fmt(total);
}

document.addEventListener('DOMContentLoaded', generateDaytourInclusions);
</script>
@endsection