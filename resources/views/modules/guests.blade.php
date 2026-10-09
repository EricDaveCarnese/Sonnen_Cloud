@extends('layouts.app')

@section('page-title', 'Guest Directory')

@section('content')
<div class="table-card">
    <div class="top-table">
        <h3>Registered Guests</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Contact Number</th>
                    <th>Email Address</th>
                    <th>Source</th>
                    <th>Total Bookings</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guests as $guest)
                <tr>
                    <td>G-{{ str_pad($guest->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $guest->first_name }} {{ $guest->last_name }}</td>
                    <td>{{ $guest->contact_number }}</td>
                    <td>{{ $guest->email_address ?? 'N/A' }}</td>
                    <td><span class="room-tag">{{ ucfirst($guest->source) }}</span></td>
                    <td>{{ $guest->bookings_count ?? 0 }}</td>
                    <td>{{ $guest->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">No registered guests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection