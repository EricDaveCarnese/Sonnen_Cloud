<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        // Today-only metrics for the cards
        $todaySales    = Payment::whereDate('payment_date', $today)->sum('amount_paid');
        $todayBookings = Booking::whereDate('created_at', $today)->count();
        $todayOrders   = Order::whereDate('created_at', $today)->count();
        $totalStaffs   = User::count();

        // Alerts
        $pendingDownpayments = Booking::where('booking_status', 'pending')->count();
        $overdueBookings     = Booking::where('booking_status', 'confirmed')
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->count();
        $lowStockItems = InventoryItem::whereRaw('quantity_on_hand <= reorder_level')->get();

        // Today's tables
        $todaysBookings = Booking::with(['guest', 'user'])
            ->whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->get();

        $todaysPayments = Payment::with(['user', 'booking.guest', 'order', 'daytourBooking'])
            ->where(function ($query) use ($today) {
                $query->whereDate('payment_date', $today)
                      ->orWhereDate('created_at', $today);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('reports.dashboard', compact(
            'todaySales',
            'todayBookings',
            'todayOrders',
            'totalStaffs',
            'pendingDownpayments',
            'overdueBookings',
            'lowStockItems',
            'todaysBookings',
            'todaysPayments'
        ));
    }

    public function index(Request $request)
    {
        $user        = Auth::user();
        $isOwner     = $user->role === 'owner_manager';

        // --- Determine date range ---
        if ($isOwner) {
            // Owner: full controls
            $period = $request->get('period', 'month');

            if ($request->filled('start_date') && $request->filled('end_date')) {
                // Custom range overrides period
                $startDate   = Carbon::parse($request->get('start_date'))->startOfDay();
                $endDate     = Carbon::parse($request->get('end_date'))->endOfDay();
                $periodLabel = $startDate->format('M d, Y') . ' — ' . $endDate->format('M d, Y');
                $period      = 'custom';
            } else {
                [$startDate, $endDate, $periodLabel] = $this->resolvePeriod($period);
            }
        } else {
            // Admin/Operations: today only, no controls
            $startDate   = Carbon::today()->startOfDay();
            $endDate     = Carbon::today()->endOfDay();
            $periodLabel = 'Today';
            $period      = 'today';
        }

        // --- Determine filters ---
        $categoryFilter = $isOwner ? $request->get('category') : null;
        $staffFilter    = $isOwner ? $request->get('staff_id') : null;

        // --- 1. Sales Summary ---
        $paymentsBase = Payment::whereBetween('payment_date', [$startDate, $endDate]);

        // Category filter for sales summary: rooms / fb / daytours
        if ($categoryFilter === 'rooms') {
            $paymentsBase->whereNotNull('booking_id');
        } elseif ($categoryFilter === 'fb') {
            $paymentsBase->whereNotNull('order_id');
        } elseif ($categoryFilter === 'daytours') {
            $paymentsBase->whereNotNull('daytour_booking_id');
        }

        // Staff filter
        if ($staffFilter) {
            $paymentsBase->where('user_id', $staffFilter);
        }

        $salesSummary = [
            'total_income'   => (clone $paymentsBase)->sum('amount_paid'),
            'total_expenses' => PurchaseOrder::where('purchase_order_status', 'received')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
                ->sum('total_cost'),
            'payment_count'  => (clone $paymentsBase)->count(),
        ];
        $salesSummary['net'] = $salesSummary['total_income'] - $salesSummary['total_expenses'];

        // --- 2. Sales by Category (always shows all three, breakdown) ---
        $salesByCategory = [
            'Rooms (Bookings)' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->whereNotNull('booking_id')
                ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
                ->sum('amount_paid'),
            'Food & Beverage'  => Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->whereNotNull('order_id')
                ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
                ->sum('amount_paid'),
            'Day Tours'        => Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->whereNotNull('daytour_booking_id')
                ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
                ->sum('amount_paid'),
        ];

        // --- 3. Top Selling Items ---
        $topItems = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('order', function ($q) use ($startDate, $endDate, $staffFilter) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
                if ($staffFilter) {
                    $q->where('user_id', $staffFilter);
                }
            })
            ->with('menuItem:id,item_name,category')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        // --- 4. Inventory Usage ---
        $inventoryUsage = InventoryMovement::select(
                'inventory_item_id',
                DB::raw('SUM(ABS(quantity_change)) as total_used')
            )
            ->where('movement_type', 'order_deduction')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
            ->with('inventoryItem:id,item_name,unit_of_measure')
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_used')
            ->take(15)
            ->get();

        // --- 5. Low Stock Alerts ---
        $lowStockItems = InventoryItem::whereRaw('quantity_on_hand <= reorder_level')
            ->orderBy('quantity_on_hand')
            ->get();

        // --- 6. Staff Performance ---
        $staffPerformance = Payment::select(
                'user_id',
                DB::raw('COUNT(*) as payment_count'),
                DB::raw('SUM(amount_paid) as total_collected')
            )
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
            ->with('user:id,fullname,role')
            ->groupBy('user_id')
            ->orderByDesc('total_collected')
            ->get();

        // --- Filter dropdown data (for owner UI) ---
        $staffOptions = $isOwner
            ? User::orderBy('fullname')->get(['id', 'fullname', 'role'])
            : collect();

        return view('reports.index', compact(
            'isOwner',
            'period',
            'periodLabel',
            'startDate',
            'endDate',
            'categoryFilter',
            'staffFilter',
            'staffOptions',
            'salesSummary',
            'salesByCategory',
            'topItems',
            'inventoryUsage',
            'lowStockItems',
            'staffPerformance'
        ));
    }

    private function resolvePeriod(string $period): array
    {
        switch ($period) {
            case 'today':
                return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay(), 'Today'];

            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), 'This Week'];

            case 'year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), 'This Year'];

            case 'all':
                return [Carbon::create(2020, 1, 1), Carbon::now()->endOfDay(), 'All Time'];

            case 'month':
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), 'This Month'];
        }
    }
}