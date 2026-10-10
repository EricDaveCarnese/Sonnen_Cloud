@extends('layouts.app')

@section('page-title', 'Booking Management')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
@endif

<!-- Add New Booking Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Create New Guest Reservation</h3>
        <span style="font-size: 13px; color: var(--accent-gold-primary);">
            <i class="fa-solid fa-clock"></i> Check-in: 1:00 PM | Check-out: 12:00 NN
        </span>
    </div>
    <form action="{{ route('bookings.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 14px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-input" placeholder="e.g. Maria" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-input" placeholder="e.g. Santos" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Contact Number (Directory Key)</label>
                <input type="text" name="contact_number" class="form-input" placeholder="0917xxxxxxx" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Email Address (Optional)</label>
                <input type="email" name="email_address" class="form-input" placeholder="guest@example.com" />
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 14px; align-items: start;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Accommodation Package</label>
                <select name="package_tier" id="package_tier" class="form-input select-dark" required onchange="calculateBookingTotal()">
                    <option value="1500">Good for 2 Persons — ₱ 1,500.00 / night</option>
                    <option value="2500">Good for 4 Persons — ₱ 2,500.00 / night</option>
                    <option value="4000">Good for 6 Persons — ₱ 4,000.00 / night</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Check-in Date</label>
                <input type="date" name="check_in_date" id="check_in_date" class="form-input" required onchange="calculateBookingTotal()" />
                <span style="font-size: 11px; color: var(--accent-gold-primary); display: block; margin-top: 4px;">Fixed at 1:00 PM</span>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Check-out Date</label>
                <input type="date" name="check_out_date" id="check_out_date" class="form-input" required onchange="calculateBookingTotal()" />
                <span style="font-size: 11px; color: var(--accent-gold-primary); display: block; margin-top: 4px;">Fixed at 12:00 NN</span>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Booking Source</label>
                <select name="source" class="form-input select-dark" required>
                    <option value="walk-in">Walk-in</option>
                    <option value="online">Online Reservation</option>
                    <option value="phone">Phone Call</option>
                </select>
            </div>
        </div>

        <!-- Live Calculation Summary -->
        <div style="background: rgba(46,34,24,0.7); border: 1px solid var(--accent-gold-border); border-radius: 10px; padding: 14px 20px; margin-top: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <span style="color: var(--text-muted); font-size: 13px;">Selected Package: </span>
                <strong id="summary-tier" style="color: var(--accent-gold-primary);">Good for 2 Persons (₱ 1,500.00/night)</strong>
                <span style="color: var(--text-muted); font-size: 12px; margin-left: 10px;">
                    | Stay: <strong id="summary-nights" style="color: #fff;">1</strong> night(s)
                </span>
            </div>
            <div>
                <span style="color: var(--text-muted); font-size: 13px;">Total Amount: </span>
                <strong id="summary-total" style="font-size: 18px; color: #fff;">₱ 1,500.00</strong>
                <span style="margin-left: 18px; color: var(--accent-gold-primary); font-size: 13px;">Mandatory 50% Downpayment: </span>
                <strong id="summary-downpayment" style="font-size: 18px; color: var(--accent-gold-primary);">₱ 750.00</strong>
            </div>
        </div>

        <div style="margin-top: 16px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 28px;">
                <i class="fa-solid fa-calendar-check"></i> Confirm Reservation & Save to Guest Directory
            </button>
        </div>
    </form>
</div>

<!-- Bookings Table -->
<div class="table-card">
    <div class="top-table">
        <h3>Reservations & Downpayments</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Guest Name</th>
                    <th>Check-in (1:00 PM)</th>
                    <th>Check-out (12:00 NN)</th>
                    <th>Total Amount</th>
                    <th>Required 50% Downpayment</th>
                    <th>Status</th>
                    <th>Breakfast</th>
                    <th>Record Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td>BK-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <strong>{{ $booking->guest->first_name }} {{ $booking->guest->last_name }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $booking->guest->contact_number }}</div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }} 1:00 PM</td>
                    <td>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }} 12:00 NN</td>
                    <td>₱ {{ number_format($booking->total_amount, 2) }}</td>
                    <td>₱ {{ number_format($booking->required_downpayment, 2) }}</td>
                    <td><span class="room-tag">{{ ucfirst($booking->booking_status) }}</span></td>
                    <td>
                        @if($booking->breakfastOrder)
                            <span style="color: #10b981; font-size: 12px; font-weight: 600;">
                                <i class="fa-solid fa-check-circle"></i>
                                {{ ucfirst($booking->breakfastOrder->status) }}
                            </span>
                        @elseif($booking->booking_status === 'confirmed' && $booking->remaining_balance > 0)
                            <a href="{{ route('breakfast.select', $booking->id) }}" class="btn-portal"
                                style="padding: 4px 10px; font-size: 11px; white-space: nowrap;">
                                <i class="fa-solid fa-egg"></i> Select
                            </a>
                        @else
                            <span style="color: var(--text-muted); font-size: 11px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($booking->booking_status !== 'completed')
                        @php
                            $isFirstPayment = ($booking->payments_sum_amount_paid ?? 0) <= 0;
                            $minAcceptable  = $isFirstPayment ? $booking->required_downpayment : 0.01;
                            $placeholder    = $isFirstPayment
                                ? 'Min: ' . number_format($booking->required_downpayment, 2)
                                : 'Amount';
                        @endphp
                        <form action="{{ route('bookings.payment', $booking->id) }}" method="POST"
                            style="display:flex; gap:6px; justify-content:center;">
                            @csrf
                            <input type="number" step="0.01" name="amount_paid"
                                placeholder="{{ $placeholder }}"
                                min="{{ $minAcceptable }}"
                                required
                                style="width: 95px; padding: 4px; border-radius: 4px;" />
                            <select name="payment_method" required style="padding: 4px; border-radius: 4px;">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank</option>
                            </select>
                            <input type="text" name="manual_receipt_no" placeholder="Receipt #" required
                                style="width: 80px; padding: 4px; border-radius: 4px;" />
                            <button type="submit" class="btn-portal" style="padding: 4px 8px;">Pay</button>
                        </form>
                        @else
                        <span style="color: #10b981; font-weight: 600;">Fully Settled</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">No booking records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Breakfast Modal Container -->
<div id="breakfast-modal-overlay" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
">
    <div id="breakfast-modal-content" style="
        background: #1a1a1a;
        border: 1px solid var(--accent-gold-border);
        border-radius: 12px;
        max-width: 1100px;
        width: 100%;
        position: relative;
        box-shadow: 0 20px 60px rgba(0,0,0,0.8);
    ">
        <button type="button" onclick="closeBreakfastModal()" style="
            position: absolute;
            top: 12px; right: 12px;
            background: transparent;
            border: none;
            color: var(--accent-gold-primary);
            font-size: 22px;
            cursor: pointer;
            z-index: 1;
        "><i class="fa-solid fa-xmark"></i></button>
        <div id="breakfast-modal-body">
            <div style="padding: 60px; text-align: center; color: var(--accent-gold-primary);">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p style="margin-top: 12px;">Loading breakfast form...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function calculateBookingTotal() {
    const tierSelect = document.getElementById('package_tier');
    const rate       = parseFloat(tierSelect.value) || 1500;
    const tierText   = tierSelect.options[tierSelect.selectedIndex].text;
    const checkInVal  = document.getElementById('check_in_date').value;
    const checkOutVal = document.getElementById('check_out_date').value;

    let nights = 1;
    if (checkInVal && checkOutVal) {
        const diff = Math.ceil((new Date(checkOutVal) - new Date(checkInVal)) / 86400000);
        nights = diff > 0 ? diff : 1;
    }

    const total       = rate * nights;
    const downpayment = total * 0.50;
    const fmt = n => '₱ ' + n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});

    document.getElementById('summary-tier').innerText        = tierText;
    document.getElementById('summary-nights').innerText      = nights;
    document.getElementById('summary-total').innerText       = fmt(total);
    document.getElementById('summary-downpayment').innerText = fmt(downpayment);
}

document.addEventListener('DOMContentLoaded', function () {
    const today    = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    const ci = document.getElementById('check_in_date');
    const co = document.getElementById('check_out_date');
    if (ci && !ci.value) ci.value = today;
    if (co && !co.value) co.value = tomorrow;
    calculateBookingTotal();
});
</script>
@endsection