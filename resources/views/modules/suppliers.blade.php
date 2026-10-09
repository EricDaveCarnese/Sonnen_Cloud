@extends('layouts.app')

@section('page-title', 'Suppliers Directory')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<!-- Add Supplier Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Register New Supplier</h3>
    </div>
    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 12px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Company / Farm Name</label>
                <input type="text" name="supplier_name" class="form-input" placeholder="e.g. Marilog Organic Farm" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Contact Person</label>
                <input type="text" name="contact_person" class="form-input" placeholder="e.g. Roberto Silva" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Phone Number</label>
                <input type="text" name="phone_number" class="form-input" placeholder="0917xxxxxxx" required />
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="supplier@domain.ph" />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Physical Address</label>
                <input type="text" name="address" class="form-input" placeholder="Barangay, City, Province" />
            </div>
        </div>
        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Register Supplier</button>
        </div>
    </form>
</div>

<!-- Supplier List -->
<div class="table-card">
    <div class="top-table">
        <h3>Registered Suppliers</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company / Farm Name</th>
                    <th>Contact Person</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Total POs</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>SUP-{{ str_pad($supplier->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $supplier->supplier_name }}</strong></td>
                    <td>{{ $supplier->contact_person }}</td>
                    <td>{{ $supplier->phone_number }}</td>
                    <td>{{ $supplier->email ?? 'N/A' }}</td>
                    <td>{{ $supplier->address ?? 'N/A' }}</td>
                    <td>{{ $supplier->purchase_orders_count ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">No suppliers registered.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection