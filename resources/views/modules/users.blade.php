@extends('layouts.app')

@section('page-title', 'User Accounts')

@section('content')
@if(session('success'))
    <div class="auth-alert success" style="margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="auth-alert error" style="margin-bottom: 20px;">
        {{ $errors->first() }}
    </div>
@endif

<!-- Add New Staff Form -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Register New Staff Account</h3>
    </div>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Full Name</label>
                <input type="text" name="fullname" class="form-input" placeholder="e.g. Juan Perez" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Username</label>
                <input type="text" name="username" class="form-input" placeholder="e.g. jperez" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>System Role</label>
                <select name="role" class="form-input select-dark" required>
                    <option value="admin">Frontdesk Admin Staff</option>
                    <option value="operations">Operations Staff</option>
                    <option value="owner_manager">Business Owner / Manager</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Initial Password</label>
                <input type="password" name="password" class="form-input" placeholder="At least 6 characters" required />
            </div>
        </div>
        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Create Staff Account</button>
        </div>
    </form>
</div>

<!-- Staff List -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Staff Accounts & Roles</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>System Role</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>U-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $user->fullname }}</td>
                    <td>{{ $user->username }}</td>
                    <td><span class="room-tag">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span></td>
                    <td>{{ ucfirst($user->status) }}</td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">No staff accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Employees (Non-System Records) -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3>Employee Records (No System Access)</h3>
    </div>
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1.5fr 1.5fr 1fr; gap: 14px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Full Name</label>
                <input type="text" name="fullname" class="form-input" placeholder="e.g. Maria Lopez" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Role / Position</label>
                <input type="text" name="role" class="form-input" placeholder="e.g. Cook, Cleaner, Gardener" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Contact Number</label>
                <input type="text" name="contact_number" class="form-input" placeholder="0917xxxxxxx" required />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Status</label>
                <select name="status" class="form-input select-dark" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-top: 12px; margin-bottom: 0;">
            <label>Notes (Optional)</label>
            <input type="text" name="notes" class="form-input" placeholder="Any additional information" />
        </div>
        <div style="margin-top: 14px; text-align: right;">
            <button type="submit" class="btn-submit" style="width: auto; padding: 10px 24px;">Add Employee Record</button>
        </div>
    </form>
</div>

<!-- Employee List -->
<div class="table-card">
    <div class="top-table">
        <h3>All Employee Records</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Role / Position</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Added On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td>EMP-{{ str_pad($employee->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $employee->fullname }}</td>
                    <td><span class="room-tag">{{ $employee->role }}</span></td>
                    <td>{{ $employee->contact_number }}</td>
                    <td>{{ ucfirst($employee->status) }}</td>
                    <td style="font-size: 12px; color: var(--text-muted);">{{ $employee->notes ?? '—' }}</td>
                    <td>{{ $employee->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">No employee records yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection