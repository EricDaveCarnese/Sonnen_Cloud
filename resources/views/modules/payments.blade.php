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
@if(Auth::user()->role === 'operations')
<div style="margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px;">
    <button type="button" onclick="openDaytourModal()" class="btn-submit" style="width: auto; padding: 10px 24px;">
        <i class="fa-solid fa-sun"></i> Day Tour Ticket
    </button>
</div>
@endif

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
                    <th class="text-left">Linked Entity</th>
                    <th class="text-right">Amount Paid</th>
                    <th>Type</th>
                    <th>Method</th>
                    <th class="text-left">Recorded By</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>PAY-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $payment->manual_receipt_no }}</strong></td>
                    <td class="text-left">
                        @if($payment->booking_id)
                            Booking #BK-{{ str_pad($payment->booking_id, 4, '0', STR_PAD_LEFT) }}
                        @elseif($payment->daytour_booking_id)
                            Day Tour #DT-{{ str_pad($payment->daytour_booking_id, 4, '0', STR_PAD_LEFT) }}
                        @elseif($payment->order_id)
                            POS Order #ORD-{{ str_pad($payment->order_id, 4, '0', STR_PAD_LEFT) }}
                        @else
                            Direct
                        @endif
                    </td>
                    <td class="text-right">₱ {{ number_format($payment->amount_paid, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                    <td><span class="room-tag">{{ strtoupper($payment->payment_method) }}</span></td>
                    <td class="text-left">{{ $payment->user->fullname ?? 'System' }}</td>
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
        max-width: 600px;
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
            <h3><i class="fa-solid fa-ticket"></i> Issue Day Tour Ticket (Gate)</h3>
        </div>

        <form action="{{ route('daytour.store') }}" method="POST" id="daytour-modal-form">
            @csrf
            <input type="hidden" name="payment_method" value="cash" />

            <div style="text-align: center; padding: 20px 0;">
                <!-- Ticket Number Display -->
                <div style="margin-bottom: 12px;">
                    <span style="background: rgba(255, 184, 0, 0.15); color: var(--accent-gold-primary); padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; border: 1px solid var(--accent-gold-border);">
                        Ticket # {{ $nextTicketNo }}
                    </span>
                </div>

                <h1 style="font-size: 48px; color: var(--accent-gold-primary); margin-bottom: 8px;">₱ 100.00</h1>
                <p style="color: var(--text-muted); font-size: 14px;">1 Entrance Ticket + Free Drink + Suman</p>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="btn-submit" style="padding: 12px 40px; font-size: 16px; width: 100%;">
                    Pay
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openDaytourModal() {
    const overlay = document.getElementById('daytour-modal-overlay');
    overlay.style.display = 'flex';
}

function closeDaytourModal() {
    document.getElementById('daytour-modal-overlay').style.display = 'none';
}

// Close on ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeDaytourModal();
});
</script>
@endsection