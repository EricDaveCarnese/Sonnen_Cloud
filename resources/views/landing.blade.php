@extends('layouts.app')

@section('content')
<div class="landing-page">
    <!-- Top Landing Header / Navbar -->
    <header class="landing-nav">
        <div class="nav-brand">
            <div class="brand-logo">S</div>
            <div class="brand-text">
                <strong>Sonnen Berg</strong>
                <span>Mountain View</span>
            </div>
        </div>
        <div class="nav-links">
            <a href="#about">About</a>
            <a href="#amenities">Amenities</a>
            <a href="#accommodations">Accommodations</a>
            <a href="#contact">Contact</a>
        </div>
        <div class="nav-auth-actions">
            <button class="btn-portal" onclick="openAuthModal('login')">
                <i class="fa-solid fa-lock"></i> Staff Portal
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="hero-badge"><i class="fa-solid fa-mountain-sun"></i> Welcome to Paradise</span>
            <h1 class="hero-heading">Escape to the Cloud Peaks of <span>Sonnen Berg</span></h1>
            <p class="hero-subheading">
                Experience unparalleled serenity, panoramic mountain views, luxury glass cabins, and exquisite dining high above the clouds.
            </p>
            <div class="hero-buttons">
                <a href="#accommodations" class="btn-primary-gold">Explore Accommodations</a>
                <button class="btn-secondary-glass" onclick="openAuthModal('login')">Management System</button>
            </div>
        </div>
    </section>

    <!-- Stats / Highlights Banner -->
    <section class="stats-banner">
        <div class="stat-item">
            <h3>2,400m</h3>
            <p>Elevation Above Sea Level</p>
        </div>
        <div class="stat-item">
            <h3>360°</h3>
            <p>Panoramic Cloudscape</p>
        </div>
        <div class="stat-item">
            <h3>100%</h3>
            <p>Serenity & Pure Air</p>
        </div>
        <div class="stat-item">
            <h3>4.9 ★</h3>
            <p>Guest Rating</p>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="landing-section">
        <div class="section-header">
            <span class="section-tag">Sanctuary Above The Clouds</span>
            <h2>A Luxury Mountain Retreat</h2>
            <p>Nestled in nature's heart, Sonnen Berg offers a serene escape from the urban rush.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-hotel"></i></div>
                <h3>Luxury Glass Villas</h3>
                <p>Architecturally designed glass suites offering unhindered morning sunrise and sea-of-clouds vistas.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-utensils"></i></div>
                <h3>Farm-To-Table Dining</h3>
                <p>Savor fresh, locally harvested gourmet meals at our signature hilltop restaurant and café.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-mug-hot"></i></div>
                <h3>Highland Coffee Lounge</h3>
                <p>Hand-crafted espresso brewed from highland beans with cozy fireside seating.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-campground"></i></div>
                <h3>Sunset Deck & Bonfire</h3>
                <p>Unwind under night-sky stars with cozy fire pits, live acoustics, and premium lounge service.</p>
            </div>
        </div>
    </section>

    <!-- Accommodations Section -->
    <section id="accommodations" class="landing-section alt-bg">
        <div class="section-header">
            <span class="section-tag">Stays & Villas</span>
            <h2>Our Signature Accommodations</h2>
            <p>Designed for comfort, romantic getaways, and family mountain retreats.</p>
        </div>

        <div class="rooms-grid">
            <div class="room-card">
                <div class="room-tag">Most Popular</div>
                <h3>The Peak Glass Villa</h3>
                <p>Features private deck, infinity bath, king bed, and 180° mountain horizons.</p>
                <div class="room-price">₱ 8,500 <span>/ night</span></div>
            </div>
            <div class="room-card">
                <div class="room-tag">Family Suite</div>
                <h3>Highland Chalet</h3>
                <p>Spacious two-bedroom timber cabin ideal for families and group retreats.</p>
                <div class="room-price">₱ 12,000 <span>/ night</span></div>
            </div>
            <div class="room-card">
                <div class="room-tag">Cozy Escape</div>
                <h3>Cloudview Dome</h3>
                <p>A modern geodesic dome suite for stargazing and romantic evenings.</p>
                <div class="room-price">₱ 6,200 <span>/ night</span></div>
            </div>
        </div>
    </section>

    <!-- Landing Footer -->
    <footer id="contact" class="landing-footer">
        <div class="footer-brand">
            <div class="logo-icon">S</div>
            <div>
                <h3>Sonnen Berg Mountain View</h3>
                <p>Highland Sanctuary & Resort ERP System</p>
            </div>
        </div>
        <p class="copyright">© {{ date('Y') }} Sonnen Berg Mountain View. All rights reserved.</p>
    </footer>
</div>

<!-- Floating Auth Modal (Login / Register) -->
<div 
    class="auth-modal-overlay" 
    id="authModal" 
    data-has-errors="{{ ($errors->any() || session('success')) ? 'true' : 'false' }}"
    onclick="closeAuthModalOnBg(event)"
>
    <div class="auth-card-wrapper">
        <button type="button" class="close-modal" onclick="closeAuthModal()"><i class="fa-solid fa-xmark"></i></button>

        <div class="modal-header">
            <div class="modal-logo">S</div>
            <h2>Sonnen Cloud ERP</h2>
            <p>Internal Staff & Management Portal</p>
        </div>

        <div class="auth-tabs">
            <button type="button" class="auth-tab active" id="tab-login" onclick="switchAuthTab('login')">Sign In</button>
            @if($registrationOpen)
            <button type="button" class="auth-tab" id="tab-register" onclick="switchAuthTab('register')">Register Staff</button>
            @endif
        </div>

        @if($errors->any())
            <div class="auth-alert error">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="auth-alert success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Login Form -->
        <form id="form-login" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input" placeholder="Enter staff username" required autofocus />
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter password" required />
            </div>

            <button type="submit" class="btn-submit">Access Dashboard</button>
        </form>

        <!-- Registration Form -->
         @if($registrationOpen)
        <form id="form-register" action="{{ route('register') }}" method="POST" style="display: none;">
            @csrf
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" class="form-input" placeholder="e.g. Maria Santos" required />
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input" placeholder="e.g. msantos" required />
            </div>

            <div class="form-group">
                <label>Assign Role</label>
                <select name="role" class="form-input select-dark" required>
                    @foreach($availableRoles as $role)
                        <option value="{{ $role }}">
                            @if($role === 'owner_manager') Owner / Manager
                            @elseif($role === 'admin') Frontdesk Admin
                            @elseif($role === 'operations') Operations / Inventory Staff
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input" placeholder="At least 6 characters" required />
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Confirm password" required />
            </div>

            <button type="submit" class="btn-submit">Register Account</button>
        </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function openAuthModal(tab = 'login') {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.add('active');
        switchAuthTab(tab);
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function closeAuthModalOnBg(e) {
    if (e.target.id === 'authModal') {
        closeAuthModal();
    }
}

function switchAuthTab(type) {
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const tabLogin = document.getElementById('tab-login');
    const tabRegister = document.getElementById('tab-register');

    if (type === 'login') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        tabLogin.classList.add('active');
        tabRegister.classList.remove('active');
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        tabRegister.classList.add('active');
        tabLogin.classList.remove('active');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('authModal');
    if (modalElement && modalElement.dataset.hasErrors === 'true') {
        openAuthModal('login');
    }
});
</script>
@endsection