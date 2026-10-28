@extends('layouts.app')

@section('page-title', 'System Audit Logs')

@section('content')
<div class="table-card">
    <div class="top-table">
        <h3><i class="fa-solid fa-shield-halved"></i> System Activity Log</h3>
        <span style="font-size: 13px; color: var(--text-muted);">Tracking all system actions</span>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>Staff Name</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                    <td><strong>{{ $log->user->fullname ?? 'System' }}</strong></td>
                    <td><span class="room-tag">{{ ucwords(str_replace('_', ' ', $log->user->role ?? 'N/A')) }}</span></td>
                    <td>
                        @if($log->action === 'Logged In')
                            <span style="color: #10b981; font-weight: 600;"><i class="fa-solid fa-right-to-bracket"></i> {{ $log->action }}</span>
                        @elseif($log->action === 'Logged Out')
                            <span style="color: #ef4444; font-weight: 600;"><i class="fa-solid fa-right-from-bracket"></i> {{ $log->action }}</span>
                        @else
                            <span style="color: var(--accent-gold-primary); font-weight: 600;">{{ $log->action }}</span>
                        @endif
                    </td>
                    <td>{{ $log->description ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No activity logs recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div style="margin-top: 16px; display: flex; justify-content: center;">
        {{ $logs->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection