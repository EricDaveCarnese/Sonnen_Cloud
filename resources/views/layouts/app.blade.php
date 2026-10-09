<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sonnen Cloud ERP System</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      crossorigin="anonymous"
    />
</head>
<body>
    <div class="sonnen">
        @auth
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon">S</div>
                <div class="logo-text">
                    Sonnen Cloud
                    <span>ERP System</span>
                </div>
            </div>

            <nav class="nav-menu">
                <div class="nav-section">
                    {{-- 1. Dashboard --}}
                    <a class="{{ request()->routeIs('reports.dashboard') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('reports.dashboard') }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>

                    {{-- 2. Guest Directory (Owner/Manager & Admin) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin']))
                    <a class="{{ request()->routeIs('guests.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('guests.index') }}">
                        <i class="fa-solid fa-user-group"></i>
                        <span>Guest Directory</span>
                    </a>
                    @endif

                    {{-- 3. User Accounts (Owner/Manager Only) --}}
                    @if(Auth::user()->role === 'owner_manager')
                    <a class="{{ request()->routeIs('users.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('users.index') }}">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>User Accounts</span>
                    </a>
                    @endif

                    {{-- 4. Booking Management (Owner/Manager & Admin) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin']))
                    <a class="{{ request()->routeIs('bookings.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('bookings.index') }}">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Booking Management</span>
                    </a>
                    @endif

                    {{-- 5. Payment Records (Owner/Manager & Admin) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin']))
                    <a class="{{ request()->routeIs('payments.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('payments.index') }}">
                        <i class="fa-solid fa-money-bill-1-wave"></i>
                        <span>Payment Records</span>
                    </a>
                    @endif

                    {{-- 6. Food & Beverage Orders (Owner/Manager & Admin) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin']))
                    <a class="{{ request()->routeIs('orders.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('orders.index') }}">
                        <i class="fa-solid fa-utensils"></i>
                        <span>Food & Beverage Orders</span>
                    </a>
                    @endif

                    {{-- 7. Menu Items (Owner/Manager & Operations) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'operations']))
                    <a class="{{ request()->routeIs('menu.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('menu.index') }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Menu Items</span>
                    </a>
                    @endif

                    {{-- 8. Inventory Items (Owner/Manager & Operations) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'operations']))
                    <a class="{{ request()->routeIs('inventory.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('inventory.index') }}">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Inventory Items</span>
                    </a>
                    @endif

                    {{-- 9. Suppliers (Owner/Manager & Operations) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'operations']))
                    <a class="{{ request()->routeIs('suppliers.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('suppliers.index') }}">
                        <i class="fa-solid fa-truck"></i>
                        <span>Suppliers</span>
                    </a>
                    @endif

                    {{-- 10. Purchase Orders (Owner/Manager only) --}}
                    @if(Auth::user()->role === 'owner_manager')
                    <a class="{{ request()->routeIs('po.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('po.index') }}">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>Purchase Orders</span>
                    </a>
                    @endif
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-logo">
                    {{ strtoupper(substr(Auth::user()->fullname, 0, 2)) }}
                </div>
                <div class="user-info">
                    <p>{{ Auth::user()->fullname }}</p>
                    <span>{{ ucwords(str_replace('_', ' ', Auth::user()->role)) }}</span>
                </div>
            </div>
        </aside>
        @endauth

        <div class="{{ Auth::check() ? 'main' : 'main-full' }}">
            @auth
            <header class="header">
                <div class="header-left">
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="header-right">
                    <i class="fa-regular fa-sun"></i>
                    <span>Sonnen Berg Mountain View</span>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: 10px;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: rgba(217,119,6,1); cursor: pointer; font-weight: 600;">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </header>
            @endauth

            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>