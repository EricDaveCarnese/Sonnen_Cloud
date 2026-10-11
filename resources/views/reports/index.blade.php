@extends('layouts.app')

@section('page-title', 'Reports')

@section('content')
<!-- Header with Period + Filters -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="top-table">
        <h3><i class="fa-solid fa-chart-line"></i> Reports — {{ $periodLabel }}</h3>
    </div>

    @if($isOwner)
        <form method="GET" action="{{ route('reports.index') }}" style="padding: 16px 20px;">

            {{-- Quick period buttons --}}
            <div style="display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap;">
                <a href="{{ route('reports.index', ['period' => 'today', 'category' => $categoryFilter, 'staff_id' => $staffFilter]) }}"
                   class="{{ $period === 'today' ? 'btn-submit' : 'btn-portal' }}"
                   style="padding: 8px 16px; font-size: 13px; text-decoration: none;">Today</a>
                <a href="{{ route('reports.index', ['period' => 'week', 'category' => $categoryFilter, 'staff_id' => $staffFilter]) }}"
                   class="{{ $period === 'week' ? 'btn-submit' : 'btn-portal' }}"
                   style="padding: 8px 16px; font-size: 13px; text-decoration: none;">This Week</a>
                <a href="{{ route('reports.index', ['period' => 'month', 'category' => $categoryFilter, 'staff_id' => $staffFilter]) }}"
                   class="{{ $period === 'month' ? 'btn-submit' : 'btn-portal' }}"
                   style="padding: 8px 16px; font-size: 13px; text-decoration: none;">This Month</a>
                <a href="{{ route('reports.index', ['period' => 'year', 'category' => $categoryFilter, 'staff_id' => $staffFilter]) }}"
                   class="{{ $period === 'year' ? 'btn-submit' : 'btn-portal' }}"
                   style="padding: 8px 16px; font-size: 13px; text-decoration: none;">This Year</a>
                <a href="{{ route('reports.index', ['period' => 'all', 'category' => $categoryFilter, 'staff_id' => $staffFilter]) }}"
                   class="{{ $period === 'all' ? 'btn-submit' : 'btn-portal' }}"
                   style="padding: 8px 16px; font-size: 13px; text-decoration: none;">All Time</a>
            </div>

            {{-- Custom date range + filters --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.5fr auto; gap: 12px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Custom Start Date</label>
                    <input type="date" name="start_date" class="form-input" value="{{ request('start_date') }}" />
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Custom End Date</label>
                    <input type="date" name="end_date" class="form-input" value="{{ request('end_date') }}" />
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Category</label>
                    <select name="category" class="form-input select-dark">
                        <option value="">All Categories</option>
                        <option value="rooms" {{ $categoryFilter === 'rooms' ? 'selected' : '' }}>Rooms Only</option>
                        <option value="fb" {{ $categoryFilter === 'fb' ? 'selected' : '' }}>Food & Beverage Only</option>
                        <option value="daytours" {{ $categoryFilter === 'daytours' ? 'selected' : '' }}>Day Tours Only</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 12px;">Staff</label>
                    <select name="staff_id" class="form-input select-dark">
                        <option value="">All Staff</option>
                        @foreach($staffOptions as $staff)
                            <option value="{{ $staff->id }}" {{ (string) $staffFilter === (string) $staff->id ? 'selected' : '' }}>
                                {{ $staff->fullname }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-submit" style="padding: 10px 20px;">Apply</button>
            </div>

            <div style="font-size: 12px; color: var(--text-muted); margin-top: 12px;">
                Showing data from <strong>{{ $startDate->format('M d, Y') }}</strong> to <strong>{{ $endDate->format('M d, Y') }}</strong>.
                Leave dates blank to use the period buttons above.
            </div>
        </form>
    @else
        <div style="padding: 16px 20px; font-size: 13px; color: var(--text-muted);">
            <i class="fa-solid fa-info-circle"></i>
            You are viewing <strong style="color: var(--accent-gold-primary);">today's</strong> reports only.
            Full history is available to the Owner/Manager.
        </div>
    @endif
</div>

<!-- 1. Sales Summary -->
 @if($isOwner)
<div class="container-cards" style="margin-bottom: 24px;">
    <div class="card">
        <div class="card-header">Total Income</div>
        <div class="card-value">₱ {{ number_format($salesSummary['total_income'], 2) }}</div>
    </div>
    <div class="card">
        <div class="card-header">Total Expenses</div>
        <div class="card-value">₱ {{ number_format($salesSummary['total_expenses'], 2) }}</div>
    </div>
    <div class="card">
        <div class="card-header">Net Revenue</div>
        <div class="card-value" style="color: '{{ $salesSummary['net'] >= 0 ? '#10b981' : '#ef4444' }}';">
            ₱ {{ number_format($salesSummary['net'], 2) }}
        </div>
    </div>
    <div class="card">
        <div class="card-header">Payments Count</div>
        <div class="card-value">{{ $salesSummary['payment_count'] }}</div>
    </div>
</div>
@endif

@if($isOwner)
<!-- 2. Sales by Category -->
<div class="table-card" style="margin-bottom: 20px;">
    <div class="top-table"><h3>Sales by Category</h3></div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="text-align: right;">Total Sales</th>
                    <th style="text-align: right;">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalCat = array_sum($salesByCategory); @endphp
                @foreach($salesByCategory as $label => $amount)
                <tr>
                    <td>{{ $label }}</td>
                    <td style="text-align: right;">₱ {{ number_format($amount, 2) }}</td>
                    <td style="text-align: right;">
                        {{ $totalCat > 0 ? number_format(($amount / $totalCat) * 100, 1) : '0.0' }}%
                    </td>
                </tr>
                @endforeach
                <tr style="background: rgba(217, 119, 6, 0.1);">
                    <td><strong>Total</strong></td>
                    <td style="text-align: right;"><strong>₱ {{ number_format($totalCat, 2) }}</strong></td>
                    <td style="text-align: right;"><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- 3. Top Selling Items -->
<div class="table-card" style="margin-bottom: 20px;">
    <div class="top-table"><h3>Top 10 Selling Items</h3></div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th style="text-align: right;">Quantity Sold</th>
                    <th style="text-align: right;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topItems as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $item->menuItem->item_name ?? 'N/A' }}</strong></td>
                    <td><span class="room-tag">{{ $item->menuItem->category ?? '—' }}</span></td>
                    <td style="text-align: right;">{{ number_format($item->total_qty) }}</td>
                    <td style="text-align: right;">₱ {{ number_format($item->total_revenue, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5">No sales in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 4. Inventory Usage -->
<div class="table-card" style="margin-bottom: 20px;">
    <div class="top-table"><h3>Inventory Usage (Top 15 Consumed)</h3></div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th style="text-align: right;">Total Used</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryUsage as $usage)
                <tr>
                    <td><strong>{{ $usage->inventoryItem->item_name ?? 'N/A' }}</strong></td>
                    <td style="text-align: right;">
                        {{ number_format($usage->total_used, 2) }}
                        {{ $usage->inventoryItem->unit_of_measure ?? '' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="2">No inventory usage in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 5. Low Stock Alerts -->
<div class="table-card" style="margin-bottom: 20px;">
    <div class="top-table">
        <h3><i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i> Low Stock Alerts</h3>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: right;">On Hand</th>
                    <th style="text-align: right;">Reorder Level</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lowStockItems as $item)
                <tr>
                    <td><strong>{{ $item->item_name }}</strong></td>
                    <td style="text-align: right;">{{ number_format($item->quantity_on_hand, 2) }} {{ $item->unit_of_measure }}</td>
                    <td style="text-align: right;">{{ number_format($item->reorder_level, 2) }} {{ $item->unit_of_measure }}</td>
                    <td>
                        @if($item->quantity_on_hand <= 0)
                            <span class="room-tag" style="background: #7f1d1d; color: #fff;">Out of Stock</span>
                        @else
                            <span class="room-tag" style="background: #ef4444; color: #fff;">Low Stock</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="color: White;">All items are sufficiently stocked.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($isOwner)
<!-- 6. Staff Performance -->
<div class="table-card" style="margin-bottom: 20px;">
    <div class="top-table"><h3>Staff Performance (Payments Processed)</h3></div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Role</th>
                    <th style="text-align: right;">Payments</th>
                    <th style="text-align: right;">Total Collected</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffPerformance as $staff)
                <tr>
                    <td><strong>{{ $staff->user->fullname ?? 'Unknown' }}</strong></td>
                    <td><span class="room-tag">{{ ucwords(str_replace('_', ' ', $staff->user->role ?? '—')) }}</span></td>
                    <td style="text-align: right;">{{ $staff->payment_count }}</td>
                    <td style="text-align: right;"><strong>₱ {{ number_format($staff->total_collected, 2) }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="4">No payments processed in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection