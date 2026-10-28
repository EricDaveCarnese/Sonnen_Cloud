@extends('layouts.app')

@section('page-title', 'Redeem Day Tour Ticket')

@section('content')
@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
@endif

<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3><i class="fa-solid fa-ticket"></i> Ticket #DT-{{ str_pad($daytour->id, 4, '0', STR_PAD_LEFT) }}</h3>
        <div style="text-align: right;">
            <span style="color: var(--text-muted); font-size: 13px;">Guest: </span>
            <strong style="font-size: 18px; color: var(--accent-gold-primary);">{{ $daytour->guest_name }}</strong>
            <br>
            <span class="room-tag" style="margin-top: 6px;">{{ $daytour->pax }} Pax</span>
        </div>
    </div>

    <form action="{{ route('daytour.submit_redeem', $daytour->id) }}" method="POST">
        @csrf
        
        <div style="background: rgba(46,34,24,0.7); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 16px; margin-bottom: 20px;">
            <h4 style="color: var(--accent-gold-primary); margin-bottom: 12px; font-size: 14px;">
                <i class="fa-solid fa-mug-hot"></i> Drink Selection (1 FREE Drink + 1 Suman per person)
            </h4>
            <div id="daytour_inclusions"></div>
        </div>

        <div style="text-align: right;">
            <a href="{{ route('orders.index') }}" class="btn-portal" style="margin-right: 12px; text-decoration: none;">Cancel</a>
            <button type="submit" class="btn-submit" style="padding: 10px 28px;">
                <i class="fa-solid fa-check-circle"></i> Confirm & Send to Kitchen
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<meta name="dt-hot-drinks" content="{{ $hotDrinks->map(fn($i) => ['name' => $i->item_name, 'available' => $i->available_servings])->toJson() }}">
<meta name="dt-cold-drinks" content="{{ $coldDrinks->map(fn($i) => ['name' => $i->item_name, 'available' => $i->available_servings])->toJson() }}">
<script>
const HOT_DRINKS  = JSON.parse(document.querySelector('meta[name="dt-hot-drinks"]').content);
const COLD_DRINKS = JSON.parse(document.querySelector('meta[name="dt-cold-drinks"]').content);
const PAX = Number("{{ $daytour->pax ?? 0 }}");

function buildDrinkOpts(type) {
    return (type === 'hot' ? HOT_DRINKS : COLD_DRINKS)
        .map(d => {
            let label = d.name;
            let disabled = '';
            if (d.available !== null) {
                if (d.available > 0) label += ` (${d.available} left)`;
                else { label += ' (out of stock)'; disabled = 'disabled'; }
            }
            return `<option value="${d.name}" data-available="${d.available ?? ''}" ${disabled}>${label}</option>`;
        })
        .join('');
}

function generateInclusions() {
    const container = document.getElementById('daytour_inclusions');
    container.innerHTML = '';
    
    for (let i = 0; i < PAX; i++) {
        const div = document.createElement('div');
        div.style.cssText = 'display:grid; grid-template-columns: auto 1fr 2fr 1.5fr; gap:10px; align-items:center; margin-bottom:8px; padding:8px; background:rgba(0,0,0,0.2); border-radius:6px;';
        div.innerHTML = `
            <span style="font-size:13px; color:var(--accent-gold-primary); font-weight:600; white-space:nowrap;">Person ${i+1}</span>
            <select name="inclusions[${i}][drink_type]" class="form-input select-dark" style="margin-bottom:0;" onchange="updateDrinkList(this,${i})">
                <option value="hot">Hot Drink</option>
                <option value="cold">Cold Drink</option>
            </select>
            <select name="inclusions[${i}][drink_name]" id="drink_name_${i}" class="form-input select-dark" style="margin-bottom:0;">
                ${buildDrinkOpts('hot')}
            </select>
            <span style="font-size:12px; color:var(--accent-gold-primary); white-space:nowrap;">
                <i class="fa-solid fa-check" style="color:#10b981;"></i> + 1 Suman
            </span>`;
        container.appendChild(div);
    }
}

function updateDrinkList(sel, index) {
    document.getElementById(`drink_name_${index}`).innerHTML = buildDrinkOpts(sel.value);
}

document.addEventListener('DOMContentLoaded', generateInclusions);
</script>
@endsection