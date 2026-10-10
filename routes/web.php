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

    // Process 5.0: Management Reporting & Insights
    Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');

    // Process 1.0, 2.0 & 3.0: Guest Reservations & F&B POS Terminal (Owner & Frontdesk Admin)
    Route::middleware([RoleMiddleware::class . ':owner_manager,admin'])->group(function () {
        // Guests
        Route::get('/guests', [NavigationController::class, 'guests'])->name('guests.index');

        // Bookings
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{id}/payment', [BookingController::class, 'recordPayment'])->name('bookings.payment');

        // Day Tour
        Route::get('/daytour', [DaytourController::class, 'index'])->name('daytour.index');
        Route::post('/daytour', [DaytourController::class, 'store'])->name('daytour.store');

        // Breakfast — Balance Payment + Meal Selection
        Route::get('/bookings/{id}/breakfast', [BreakfastController::class, 'selectBreakfast'])->name('breakfast.select');
        Route::post('/bookings/{id}/breakfast', [BreakfastController::class, 'submitBreakfastAndPayBalance'])->name('breakfast.submit');

        // Payments Ledger
        Route::get('/payments', [NavigationController::class, 'payments'])->name('payments.index');

        // Food & Beverage Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('/orders/{id}/payment', [OrderController::class, 'processPayment'])->name('orders.payment');
    });

    // Order & Breakfast Status Advancement (Owner, Admin, Operations)
    Route::middleware([RoleMiddleware::class . ':owner_manager,admin,operations'])->group(function () {
        Route::post('/orders/{id}/status', [OrderController::class, 'advanceStatus'])->name('orders.status');
        Route::post('/breakfast-orders/{id}/status', [BreakfastController::class, 'advanceStatus'])->name('breakfast.status');
    });

    // Process 4.0: Inventory & Supply Operations (Owner & Operations Staff)
    Route::middleware([RoleMiddleware::class . ':owner_manager,operations'])->group(function () {
        // Menu Items
        Route::get('/menu-items', [NavigationController::class, 'menu'])->name('menu.index');
        Route::post('/menu-items', [NavigationController::class, 'storeMenuItem'])->name('menu.store');

        // Inventory Stock Items
        Route::get('/inventory-items', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory-items', [NavigationController::class, 'storeInventoryItem'])->name('inventory.store');

        // Suppliers
        Route::get('/suppliers', [NavigationController::class, 'suppliers'])->name('suppliers.index');
        Route::post('/suppliers', [NavigationController::class, 'storeSupplier'])->name('suppliers.store');
    });

    // Purchase Orders: owner_manager only (view, create, receive)
    Route::middleware([RoleMiddleware::class . ':owner_manager'])->group(function () {
        Route::get('/purchase-orders', [NavigationController::class, 'purchaseOrders'])->name('po.index');
        Route::post('/purchase-orders', [InventoryController::class, 'createPO'])->name('po.store');
        Route::post('/purchase-orders/{id}/receive', [InventoryController::class, 'receivePO'])->name('po.receive');
    });

    // User Administration (Owner Only)
    Route::middleware([RoleMiddleware::class . ':owner_manager'])->group(function () {
        Route::get('/user-accounts', [NavigationController::class, 'users'])->name('users.index');
        Route::post('/user-accounts', [NavigationController::class, 'storeUser'])->name('users.store');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    });
});