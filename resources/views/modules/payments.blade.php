@extends('layouts.app')

@section('page-title', 'Payment Records')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
@endif

<!-- Quick Actions Row -->
<div style="margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px;">
    <button type="button" onclick="openDaytourModal()" class="btn-submit" style="width: auto; padding: 10px 24px;">
        <i class="fa-solid fa-sun"></i> Record Day Tour
    </button>
</div>

<div class="table-card">
    <div class="top-table">
        <h3>Consolidated Transaction Ledger</h3>
    </div>
    <div class="table-container" style="max-height: 640px; overflow-y: auto;">
        <table>
            <thead style="position: sticky; top: 0; z-index: 2;">
                <tr>
                    <th>Payment ID</th>
                    <th>Receipt Number</th>
                    <th>Linked Entity</th>
                    <th>Amount Paid</th>
                    <th>Type</th>
                    <th>Method</th>
                    <th>Recorded By</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>PAY-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $payment->manual_receipt_no }}</strong></td>
                    <td>
                        @if($payment->booking_id)
                            Booking #BK-{{ str_pad($payment->booking_id, 4, '0', STR_PAD_LEFT) }}
                        @elseif($payment->order_id)
                            POS Order #ORD-{{ str_pad($payment->order_id, 4, '0', STR_PAD_LEFT) }}
                        @elseif($payment->daytour_booking_id)
                            Day Tour #DT-{{ str_pad($payment->daytour_booking_id, 4, '0', STR_PAD_LEFT) }}
                        @else
                            Direct
                        @endif
                    </td>
                    <td>₱ {{ number_format($payment->amount_paid, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                    <td><span class="room-tag">{{ strtoupper($payment->payment_method) }}</span></td>
                    <td>{{ $payment->user->fullname ?? 'System' }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No payment transactions recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Daytour Modal -->
<div id="daytour-modal-overlay" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
" onclick="if (event.target.id === 'daytour-modal-overlay') closeDaytourModal();">

    <div class="table-card" style="
        max-width: 1100px;
        width: 100%;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
        margin: 0;
    ">
        <button type="button" onclick="closeDaytourModal()" style="
            position: absolute;
            top: 12px; right: 12px;
            background: transparent;
            border: none;
            color: var(--accent-gold-primary);
            font-size: 22px;
            cursor: pointer;
            z-index: 1;
        "><i class="fa-solid fa-xmark"></i></button>

        <div class="top-table" style="padding-right: 40px;">
            <h3><i class="fa-solid fa-sun"></i> Register Day Tour</h3>
            <span style="font-size: 13px; color: var(--accent-gold-primary);">
                <i class="fa-solid fa-ticket"></i> ₱100/person — Includes 1 Drink + 1 Suman (Malagkit)
            </span>
        </div>

        <form action="{{ route('daytour.store') }}" method="POST" id="daytour-modal-form">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1.5fr 0.6fr 1.2fr; gap: 14px; margin-bottom: 14px;">
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
                    <input type="number" name="pax" id="dt_pax" class="form-input" min="1" max="50" value="1" required onchange="generateDtInclusions()" />
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-input select-dark" required>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
            </div>

            <!-- Drink Selections per Person -->
            <div style="background: rgba(46,34,24,0.7); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 16px; margin-bottom: 14px;">
                <h4 style="color: var(--accent-gold-primary); margin-bottom: 12px; font-size: 14px;">
                    <i class="fa-solid fa-mug-hot"></i> Drink Inclusions per Person (1 FREE per person)
                </h4>
                <div id="dt_inclusions"></div>
            </div>

            <!-- Extra Add-ons -->
            <div style="background: rgba(46,34,24,0.5); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 16px; margin-bottom: 14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h4 style="color: var(--accent-gold-primary); font-size: 14px; margin:0;">
                        <i class="fa-solid fa-plus-circle"></i> Extra Orders (Paid)
                    </h4>
                    <button type="button" onclick="addDtExtra()" class="btn-portal" style="padding: 4px 12px; font-size: 12px;">+ Add Item</button>
                </div>
                <div id="dt_extras"></div>
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

            <div style="text-align: right; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeDaytourModal()" class="btn-portal" style="padding: 10px 24px;">Cancel</button>
                <button type="submit" class="btn-submit" style="width: auto; padding: 10px 28px;">
                    <i class="fa-solid fa-check-circle"></i> Confirm Day Tour & Collect Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<meta name="dt-hot-drinks" content="{{ $hotDrinks->pluck('item_name')->toJson() }}">
<meta name="dt-cold-drinks" content="{{ $coldDrinks->pluck('item_name')->toJson() }}">
<meta name="dt-paid-items" content="{{ $allPaidItems->map(fn($i) => ['name' => $i->item_name, 'price' => (float) $i->unit_price, 'category' => $i->category])->toJson() }}">
<script>
const DT_HOT_DRINKS  = JSON.parse(document.querySelector('meta[name="dt-hot-drinks"]').content);
const DT_COLD_DRINKS = JSON.parse(document.querySelector('meta[name="dt-cold-drinks"]').content);
const DT_PAID_ITEMS  = JSON.parse(document.querySelector('meta[name="dt-paid-items"]').content);
const DT_CATEGORIES  = [...new Set(DT_PAID_ITEMS.map(i => i.category))];

function buildDtDrinkOpts(type) {
    return (type === 'hot' ? DT_HOT_DRINKS : DT_COLD_DRINKS)
        .map(d => `<option value="${d}">${d}</option>`).join('');
}

function openDaytourModal() {
    const overlay = document.getElementById('daytour-modal-overlay');
    overlay.style.display = 'flex';

    // Reset form and regenerate inclusions
    const form = document.getElementById('daytour-modal-form');
    form.reset();
    document.getElementById('dt_extras').innerHTML = '';
    generateDtInclusions();
    calcDtTotal();
}

function closeDaytourModal() {
    document.getElementById('daytour-modal-overlay').style.display = 'none';
}

// Close on ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeDaytourModal();
});

function generateDtInclusions() {
    const pax = parseInt(document.getElementById('dt_pax').value) || 1;
    const container = document.getElementById('dt_inclusions');
    container.innerHTML = '';
    for (let i = 0; i < pax; i++) {
        const div = document.createElement('div');
        div.style.cssText = 'display:grid; grid-template-columns: auto 1fr 2fr; gap:10px; align-items:center; margin-bottom:8px; padding:8px; background:rgba(0,0,0,0.2); border-radius:6px;';
        div.innerHTML = `
            <span style="font-size:13px; color:var(--accent-gold-primary); font-weight:600; white-space:nowrap;">Person ${i+1}</span>
            <select name="inclusions[${i}][drink_type]" class="form-input select-dark" style="margin-bottom:0;" onchange="updateDtDrinkList(this,${i})">
                <option value="hot">Hot Drink</option>
                <option value="cold">Cold Drink</option>
            </select>
            <select name="inclusions[${i}][drink_name]" id="dt_drink_${i}" class="form-input select-dark" style="margin-bottom:0;">
                ${buildDtDrinkOpts('hot')}
            </select>`;
        container.appendChild(div);

        const note = document.createElement('div');
        note.style.cssText = 'font-size:11px; color:var(--text-muted); margin-bottom:10px; padding-left:8px;';
        note.innerHTML = '<i class="fa-solid fa-check" style="color:#10b981;"></i> 1 Suman (Malagkit) automatically included';
        container.appendChild(note);
    }
    calcDtTotal();
}

function updateDtDrinkList(sel, index) {
    document.getElementById(`dt_drink_${index}`).innerHTML = buildDtDrinkOpts(sel.value);
}

let dtExtraCount = 0;
function addDtExtra() {
    const container = document.getElementById('dt_extras');
    const idx = dtExtraCount++;
    const div = document.createElement('div');
    div.id = `dt_extra_${idx}`;
    div.style.cssText = 'display:grid; grid-template-columns: 1.2fr 2fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:8px;';

    const categoryOpts = DT_CATEGORIES.map(c => `<option value="${c}">${c}</option>`).join('');

    div.innerHTML = `
        <select id="dt_cat_${idx}" class="form-input select-dark" style="margin-bottom:0;" onchange="updateDtExtraItems(${idx})">
            <option value="">— None —</option>
            ${categoryOpts}
        </select>
        <select name="extra_orders[${idx}][name]" id="dt_name_${idx}" class="form-input select-dark" style="margin-bottom:0;" onchange="updateDtExtraPrice(${idx})">
            <option value="">— None —</option>
        </select>
        <input type="hidden" name="extra_orders[${idx}][price]" id="dt_ep_${idx}" value="0" />
        <input type="number" name="extra_orders[${idx}][quantity]" class="form-input" min="1" value="1" style="margin-bottom:0;" onchange="calcDtTotal()" />
        <button type="button" onclick="removeDtExtra(${idx})" style="background:#ef4444;border:none;color:#fff;border-radius:6px;padding:6px 10px;cursor:pointer;">
            <i class="fa-solid fa-times"></i>
        </button>`;
    container.appendChild(div);
    calcDtTotal();
}

function updateDtExtraItems(idx) {
    const cat = document.getElementById(`dt_cat_${idx}`).value;
    const nameSel = document.getElementById(`dt_name_${idx}`);
    if (! cat) {
        nameSel.innerHTML = '<option value="">— None —</option>';
    } else {
        const items = DT_PAID_ITEMS.filter(i => i.category === cat);
        nameSel.innerHTML = '<option value="">— None —</option>' +
            items.map(i => `<option value="${i.name}" data-price="${i.price}">${i.name} — ₱${i.price}</option>`).join('');
    }
    updateDtExtraPrice(idx);
}

function updateDtExtraPrice(idx) {
    const nameSel = document.getElementById(`dt_name_${idx}`);
    const opt = nameSel.options[nameSel.selectedIndex];
    const price = (opt && opt.dataset.price) ? opt.dataset.price : 0;
    document.getElementById(`dt_ep_${idx}`).value = price;
    calcDtTotal();
}

function removeDtExtra(idx) {
    const el = document.getElementById(`dt_extra_${idx}`);
    if (el) el.remove();
    calcDtTotal();
}

function calcDtTotal() {
    const pax = parseInt(document.getElementById('dt_pax').value) || 1;
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

// Initialize on page load if modal somehow visible
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('daytour-modal-overlay').style.display === 'flex') {
        generateDtInclusions();
    }
});
</script>
@endsection