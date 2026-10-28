<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BreakfastController;
use App\Http\Controllers\DaytourController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::get('/', [AuthController::class, 'showLanding'])->name('landing');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Modules
Route::middleware(['auth'])->group(function () {

    // Profile / Password Update
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
    // Process 5.0: Management Reporting & Insights
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::middleware([RoleMiddleware::class . ':owner_manager,admin'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // --- Bookings & Breakfast (Owner & Admin ONLY) ---
    Route::middleware([RoleMiddleware::class . ':owner_manager,admin,operations'])->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{id}/payment', [BookingController::class, 'recordPayment'])->name('bookings.payment');

        Route::get('/bookings/{id}/breakfast', [BreakfastController::class, 'selectBreakfast'])->name('breakfast.select');
        Route::post('/bookings/{id}/breakfast', [BreakfastController::class, 'submitBreakfastAndPayBalance'])->name('breakfast.submit');
    });

    // --- Operational modules (Owner, Admin, Operations) ---
    Route::middleware([RoleMiddleware::class . ':owner_manager,admin,operations'])->group(function () {
        // Payments Ledger
        Route::get('/payments', [NavigationController::class, 'payments'])->name('payments.index');

        // Food & Beverage Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{id}/payment', [OrderController::class, 'processPayment'])->name('orders.payment');

        // Order & Breakfast Status Advancement
        Route::post('/orders/{id}/status', [OrderController::class, 'advanceStatus'])->name('orders.status');
        Route::post('/breakfast-orders/{id}/status', [BreakfastController::class, 'advanceStatus'])->name('breakfast.status');
    });

    // --- Day Tour Ticketing (Operations ONLY) ---
    Route::middleware([RoleMiddleware::class . ':operations'])->group(function () {
        // Only Operations (Gate) can issue tickets
        Route::post('/daytour', [DaytourController::class, 'store'])->name('daytour.store');
    });

    // --- Day Tour Redemption (Admin & Operations) ---
    Route::middleware([RoleMiddleware::class . ':admin,operations'])->group(function () {
        // Both can process the drink redemption at the canteen
        Route::get('/daytour/{id}/redeem', [DaytourController::class, 'showRedeem'])->name('daytour.redeem');
        Route::post('/daytour/{id}/redeem', [DaytourController::class, 'submitRedeem'])->name('daytour.submit_redeem');
    });

    // --- Guest Directory (Owner + Admin only) ---
    Route::middleware([RoleMiddleware::class . ':owner_manager,admin'])->group(function () {
        Route::get('/guests', [NavigationController::class, 'guests'])->name('guests.index');
    });

    // --- Menu & Inventory (Owner ONLY) ---
    Route::middleware([RoleMiddleware::class . ':owner_manager'])->group(function () {

        // Audit Logs (Owner Only)
        Route::get('/audit-logs', [NavigationController::class, 'auditLogs'])->name('logs.index');
        // Menu Items
        Route::get('/menu-items', [NavigationController::class, 'menu'])->name('menu.index');
        Route::post('/menu-items', [NavigationController::class, 'storeMenuItem'])->name('menu.store');
        Route::put('/menu-items/{id}', [NavigationController::class, 'updateMenuItem'])->name('menu.update');

        // Inventory Stock Items
        Route::get('/inventory-items', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory-items', [NavigationController::class, 'storeInventoryItem'])->name('inventory.store');

        // Purchase Orders
        Route::get('/purchase-orders', [NavigationController::class, 'purchaseOrders'])->name('po.index');
        Route::post('/purchase-orders', [InventoryController::class, 'createPO'])->name('po.store');
        Route::post('/purchase-orders/{id}/receive', [InventoryController::class, 'receivePO'])->name('po.receive');
        
        // Employees
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    });

    // --- Suppliers (Owner & Operations) ---
    Route::middleware([RoleMiddleware::class . ':owner_manager,operations'])->group(function () {
        Route::get('/suppliers', [NavigationController::class, 'suppliers'])->name('suppliers.index');
        Route::post('/suppliers', [NavigationController::class, 'storeSupplier'])->name('suppliers.store');
    });

    // --- User Accounts (Owner & Admin) ---
    Route::middleware([RoleMiddleware::class . ':owner_manager,admin'])->group(function () {
        Route::get('/user-accounts', [NavigationController::class, 'users'])->name('users.index');
        Route::post('/user-accounts', [NavigationController::class, 'storeUser'])->name('users.store');
    });
});