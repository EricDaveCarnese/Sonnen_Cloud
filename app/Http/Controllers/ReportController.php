<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Booking;
use App\Models\Order;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function dashboard()
    {
        $today        = Carbon::today();
        $startOfWeek  = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Financial aggregations
        $totalSales   = Payment::sum('amount_paid');
        $dailySales   = Payment::whereDate('payment_date', $today)->sum('amount_paid');
        $weeklySales  = Payment::where('payment_date', '>=', $startOfWeek)->sum('amount_paid');
        $monthlySales = Payment::where('payment_date', '>=', $startOfMonth)->sum('amount_paid');

        // Operational counts
        $totalBookings = Booking::count();
        $totalOrders   = Order::count();
        $totalStaffs   = User::count();

        // Expenses
        $totalExpenses = PurchaseOrder::where('purchase_order_status', 'received')->sum('total_cost');
        $netRevenue    = $totalSales - $totalExpenses;

        // Operational health
        $pendingDownpayments = Booking::where('booking_status', 'pending')->count();
        $overdueBookings     = Booking::where('booking_status', 'confirmed')
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->count();

        $lowStockItems = InventoryItem::whereRaw('quantity_on_hand <= reorder_level')->get();

        // Today's transactions
        $todaysBookings = Booking::with(['guest', 'user'])
            ->whereDate('created_at', $today)
            ->orderBy('id', 'desc')
            ->get();

        $todaysPayments = Payment::with(['user', 'booking.guest', 'order'])
            ->where(function ($query) use ($today) {
                $query->whereDate('payment_date', $today)
                      ->orWhereDate('created_at', $today);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('reports.dashboard', compact(
            'totalSales',
            'dailySales',
            'weeklySales',
            'monthlySales',
            'totalBookings',
            'totalOrders',
            'totalStaffs',
            'totalExpenses',
            'netRevenue',
            'pendingDownpayments',
            'overdueBookings',
            'lowStockItems',
            'todaysBookings',
            'todaysPayments'
        ));
    }
}