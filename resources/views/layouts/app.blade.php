<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sonnen Cloud ERP System</title>
    <link rel="icon" type="image/png" href="{{ asset('source/Sonnen_Icon.png') }}">
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
        <!-- Mobile Overlay -->
        <div id="mobile-overlay" onclick="toggleMobileMenu()" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 999; backdrop-filter: blur(2px);"></div>

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
                    {{-- Reports (Owner/Manager & Admin only) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin']))
                    <a class="{{ request()->routeIs('reports.index') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('reports.index') }}">
                        <i class="fa-solid fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                    @endif

                   {{-- 4. Booking Management (Owner/Manager & Admin only) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin']))
                    <a class="{{ request()->routeIs('bookings.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('bookings.index') }}">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Booking Management</span>
                    </a>
                    @endif

                    {{-- 5. Payment Records (Owner/Manager, Admin, Operations) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin', 'operations']))
                    <a class="{{ request()->routeIs('payments.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('payments.index') }}">
                        <i class="fa-solid fa-money-bill-1-wave"></i>
                        <span>Payment Records</span>
                    </a>
                    @endif

                    {{-- 6. Food & Beverage Orders (Owner/Manager, Admin) --}}
                    @if(in_array(Auth::user()->role, ['owner_manager', 'admin', 'operations']))
                    <a class="{{ request()->routeIs('orders.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('orders.index') }}">
                        <i class="fa-solid fa-utensils"></i>
                        <span>Food & Beverage Orders</span>
                    </a>
                    @endif

                    {{-- 7. Menu Items (Owner/Manager ONLY) --}}
                    @if(Auth::user()->role === 'owner_manager')
                    <a class="{{ request()->routeIs('menu.*') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('menu.index') }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Menu Items</span>
                    </a>
                    @endif

                    {{-- 8. Inventory Items (Owner/Manager ONLY) --}}
                    @if(Auth::user()->role === 'owner_manager')
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

                    {{-- 11. Audit Logs (Owner/Manager Only) --}}
                    @if(Auth::user()->role === 'owner_manager')
                    <a class="{{ request()->routeIs('logs.index') ? 'nav-item-active' : 'nav-item' }}" href="{{ route('logs.index') }}">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Audit Logs</span>
                    </a>
                    @endif
                </div>
            </nav>

            {{-- Clickable Footer for Profile Modal --}}
            <div class="sidebar-footer" onclick="openProfileModal()" style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                <div class="user-logo">
                    {{ strtoupper(substr(Auth::user()->fullname, 0, 2)) }}
                </div>
                <div class="user-info">
                    <p>{{ Auth::user()->fullname }}</p>
                    <span>{{ ucwords(str_replace('_', ' ', Auth::user()->role)) }}</span>
                </div>
            </div>
        </aside>

        <!-- Profile Modal -->
        <div id="profile-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);" onclick="if(event.target.id === 'profile-modal') closeProfileModal();">
            
            <div style="background: var(--bg-sidebar); border: 1px solid var(--accent-gold-border); border-radius: 12px; padding: 24px; width: 100%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.8); position: relative;">
                <button type="button" onclick="closeProfileModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; color: var(--text-muted); font-size: 20px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <div class="user-logo" style="margin: 0 auto 12px auto; width: 60px; height: 60px; font-size: 20px;">
                        {{ strtoupper(substr(Auth::user()->fullname, 0, 2)) }}
                    </div>
                    <h2 style="color: var(--text-primary); font-size: 18px;">{{ Auth::user()->fullname }}</h2>
                    <p style="color: var(--accent-gold-primary); font-size: 13px; font-weight: 600;">
                        {{ ucwords(str_replace('_', ' ', Auth::user()->role)) }}
                    </p>
                </div>

                <hr style="border-color: var(--accent-gold-border); margin-bottom: 20px;">

                <h3 style="color: var(--text-primary); font-size: 14px; margin-bottom: 12px;"><i class="fa-solid fa-lock"></i> Change Password</h3>

                <!-- Server-side Validation Errors -->
                @if($errors->any())
                    <div class="auth-alert error" style="margin-bottom: 14px; font-size: 12px;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Success Notification -->
                <div id="password-success-msg" style="display: none; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #6ee7b7; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 14px; text-align: center;">
                    <i class="fa-solid fa-check-circle"></i> Password updated successfully!
                </div>

                <form action="{{ route('profile.password') }}" method="POST" id="change-password-form">
                    @csrf
                    
                    <!-- Current Password -->
                    <div class="form-group">
                        <label style="font-size: 12px;">Current Password</label>
                        <div style="position: relative;">
                            <input type="password" name="current_password" id="current_password" class="form-input" required style="padding-right: 40px;" />
                            <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('current_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="form-group">
                        <label style="font-size: 12px;">New Password</label>
                        <div style="position: relative;">
                            <input type="password" name="new_password" id="new_password" class="form-input" required minlength="6" style="padding-right: 40px;" />
                            <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('new_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label style="font-size: 12px;">Confirm New Password</label>
                        <div style="position: relative;">
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-input" required minlength="6" style="padding-right: 40px;" oninput="checkPasswordMatch()" />
                            <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('new_password_confirmation', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                        </div>
                        <!-- Live Match Status -->
                        <div id="password-match-msg" style="font-size: 12px; margin-top: 6px; display: none; font-weight: 600;"></div>
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 10px; padding: 10px; font-size: 14px;">Update Password</button>
                </form>
            </div>
        </div>
        @endauth

        <div class="{{ Auth::check() ? 'main' : 'main-full' }}">
            @auth
            <header class="header">
                <div class="header-left" style="display: flex; align-items: center;">
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                        <i class="fa-solid fa-bars"></i>
                    </button>
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

    <script>
        // --- Mobile Menu Logic ---
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            if (!sidebar) return;

            sidebar.classList.toggle('sidebar-open');
            
            if (sidebar.classList.contains('sidebar-open')) {
                overlay.style.display = 'block';
            } else {
                overlay.style.display = 'none';
            }
        }

        // --- Modal Logic ---
        function openProfileModal() {
            document.getElementById('profile-modal').style.display = 'flex';
        }
        function closeProfileModal() {
            document.getElementById('profile-modal').style.display = 'none';
        }
        
        // --- Password Toggle Logic ---
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                icon.style.color = 'var(--accent-gold-primary)';
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                icon.style.color = 'var(--text-muted)';
            }
        }

        // --- Live Password Match Logic ---
        function checkPasswordMatch() {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('new_password_confirmation').value;
            const msg = document.getElementById('password-match-msg');

            if (confirmPass.length === 0) {
                msg.style.display = 'none';
                return;
            }

            if (newPass === confirmPass) {
                msg.style.display = 'block';
                msg.style.color = '#10b981'; // Green
                msg.innerHTML = '<i class="fa-solid fa-check-circle"></i> Passwords match';
            } else {
                msg.style.display = 'block';
                msg.style.color = '#ef4444'; // Red
                msg.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Passwords do not match';
            }
        }

        // Enclosing the Blade tags in quotes convinces the editor it is just a string literal
    const openModalOnState = "{{ ($errors->has('current_password') || session('success')) ? 'true' : 'false' }}" === "true";
    const showSuccessNotification = "{{ session('success') ? 'true' : 'false' }}" === "true";

    // --- Show Success Notification and Clear Form ---
    if (showSuccessNotification) {
        document.addEventListener('DOMContentLoaded', function() {
            const successBox = document.getElementById('password-success-msg');
            if (successBox) successBox.style.display = 'block';
            
            const form = document.getElementById('change-password-form');
            if (form) form.reset();
            
            setTimeout(() => {
                if (successBox) successBox.style.display = 'none';
            }, 4000);
        });
    }
    </script>

    @yield('scripts')
</body>
</html>