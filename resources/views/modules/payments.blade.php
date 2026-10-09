@extends('layouts.app')

@section('page-title', 'Payment Records')

@section('content')
<div class="table-card">
    <div class="top-table">
        <h3>Consolidated Transaction Ledger</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
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
@endsection