@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<!-- Metric Cards in 4-Column Horizontal Layout -->
<div class="container-cards">
    <div class="card">
        <div class="card-header">Payments Received</div>
        <div class="card-value">₱ {{ number_format($totalSales ?? 14550, 2) }}</div>
    </div>
    <div class="card">
        <div class="card-header">Total Bookings</div>
        <div class="card-value">{{ $totalBookings ?? 12 }}</div>
    </div>
    <div class="card">
        <div class="card-header">Food & Beverage Orders</div>
        <div class="card-value">{{ $totalOrders ?? 12 }}</div>
    </div>
    <div class="card">
        <div class="card-header">Staffs</div>
        <div class="card-value">{{ $totalStaffs ?? 10 }}</div>
    </div>
</div>

<!-- Table 1: Today's Booking Transactions (Separate Card) -->
<div class="table-card">
    <div class="top-table">
        <h3>Today's Booking Transactions</h3>
        <a href="{{ route('bookings.index') }}">View All</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Guest Name</th>
                    <th>Recorded By</th>
                    <th>Check-in (1:00 PM)</th>
                    <th>Check-out (12:00 NN)</th>
                    <th>Total Amount</th>
                    <th>50% Downpayment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todaysBookings as $booking)
                <tr>
                    <td>BK-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <strong>{{ $booking->guest->first_name }} {{ $booking->guest->last_name }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $booking->guest->contact_number }}</div>
                    </td>
                    <td>{{ $booking->user->fullname ?? 'Admin' }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }} 1:00 PM</td>
                    <td>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('M d, Y') }} 12:00 NN</td>
                    <td>₱ {{ number_format($booking->total_amount, 2) }}</td>
                    <td>₱ {{ number_format($booking->required_downpayment, 2) }}</td>
                    <td><span class="room-tag">{{ ucfirst($booking->booking_status) }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No booking transactions recorded today.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Table 2: Today's Payment Transactions (Separate Card) -->
<div class="table-card">
    <div class="top-table">
        <h3>Today's Payment Transactions</h3>
        <a href="{{ route('payments.index') }}">View All</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Receipt Number</th>
                    <th>Linked Reference</th>
                    <th>Customer / Guest</th>
                    <th>Amount Paid</th>
                    <th>Payment Type</th>
                    <th>Method</th>
                    <th>Recorded By</th>
                    <th>Payment Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todaysPayments as $payment)
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
                    <td>
                        @if($payment->booking && $payment->booking->guest)
                            {{ $payment->booking->guest->first_name }} {{ $payment->booking->guest->last_name }}
                        @elseif($payment->order)
                            Restaurant Guest
                        @else
                            Walk-in Guest
                        @endif
                    </td>
                    <td><strong>₱ {{ number_format($payment->amount_paid, 2) }}</strong></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                    <td><span class="room-tag">{{ strtoupper($payment->payment_method) }}</span></td>
                    <td>{{ $payment->user->fullname ?? 'Admin' }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">No payment transactions recorded today.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection