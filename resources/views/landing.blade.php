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
                Experience unparalleled serenity, panoramic mountain views, and exquisite dining high above the clouds.
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
            <h3>Marilog</h3>
            <p>High-Altitude Paradise</p>
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

        <!-- Video + Description Split -->
        <div class="video-desc-split">
            <div class="video-frame">
                <video
                    controls
                    preload="metadata"
                    poster="{{ asset('source/sonnen.jpg') }}"
                    class="video-player"
                >
                    <source src="{{ asset('source/Sonnen_Berg.mp4') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <div class="video-desc-text">
                <span class="section-tag" style="display: block; margin-bottom: 12px;">Since 2011</span>
                <h3>Where the Mountains Meet the Sky</h3>
                <p>
                    Perched high above the clouds in the cool highlands of Marilog, Sonnen Berg Mountain View
                    has been the region's premier mountain sanctuary since opening its doors in 2018. What
                    began as a quiet family retreat has grown into one of the most celebrated destinations
                    for travelers seeking a break from the noise of the city.
                </p>
                <p>
                    Every sunrise here is a spectacle. Every sunset, a painting. Our signature glass villas,
                    farm-to-table cuisine, and hand-crafted highland coffee have drawn guests from across the
                    country — couples on honeymoon, families on reunion, adventurers chasing the sea of clouds.
                    Come for the view. Stay for the serenity.
                </p>
                <p>
                    <strong style="color: var(--accent-gold-primary);">
                        Ranked among the top mountain retreats in the Philippines
                    </strong>
                    — a distinction earned not just by our altitude, but by the warmth of our hospitality
                    and the memories we help create.
                </p>
            </div>
        </div>

        <div class="features-grid">
            @php $backgroundImage = asset('source/sonnen-1.jpg'); @endphp
            <div class="feature-card-image" style="background-image: url('{{ $backgroundImage }}');">
                <div class="feature-card-overlay"></div>
            </div>
            @php $backgroundImage = asset('source/sonnen-2.jpg'); @endphp
            <div class="feature-card-image" style="background-image: url('{{ $backgroundImage }}');">
                <div class="feature-card-overlay"></div>
            </div>
            @php $backgroundImage = asset('source/sonnen-3.jpg'); @endphp
            <div class="feature-card-image" style="background-image: url('{{ $backgroundImage }}');">
                <div class="feature-card-overlay"></div>
            </div>
            @php $backgroundImage = asset('source/sonnen-4.jpg'); @endphp
            <div class="feature-card-image" style="background-image: url('{{ $backgroundImage }}');">
                <div class="feature-card-overlay"></div>
            </div>
        </div>
    </section>

    <!-- Amenities Section -->
    <section id="amenities" class="landing-section alt-bg">
        <div class="section-header">
            <span class="section-tag">What Awaits You</span>
            <h2>Resort Amenities</h2>
            <p>Every detail crafted for your comfort and unforgettable stay.</p>
        </div>

        <div class="amenities-grid">
            <div class="amenity-card">
                <div class="amenity-icon"><i class="fa-solid fa-wifi"></i></div>
                <h4>High-Speed Wi-Fi</h4>
                <p>Complimentary connectivity in all guest areas and villas.</p>
            </div>
            <div class="amenity-card">
                <div class="amenity-icon"><i class="fa-solid fa-utensils"></i></div>
                <h4>Restaurant & Café</h4>
                <p>Fresh farm-to-table dining and signature highland coffee.</p>
            </div>
            <div class="amenity-card">
                <div class="amenity-icon"><i class="fa-solid fa-mountain-sun"></i></div>
                <h4>Sunrise & Sunset Deck</h4>
                <p>Panoramic viewing decks facing the sea of clouds.</p>
            </div>
            <div class="amenity-card">
                <div class="amenity-icon"><i class="fa-solid fa-car"></i></div>
                <h4>Free Parking</h4>
                <p>Ample secure parking on resort grounds.</p>
            </div>
            <div class="amenity-card">
                <div class="amenity-icon"><i class="fa-solid fa-camera-retro"></i></div>
                <h4>Scenic Photo Spots</h4>
                <p>Instagram-worthy views at every corner of the resort.</p>
            </div>
            <div class="amenity-card">
                <div class="amenity-icon"><i class="fa-solid fa-leaf"></i></div>
                <h4>Nature Trails</h4>
                <p>Guided forest walks around the highland property.</p>
            </div>
        </div>
    </section>

    <!-- Accommodations Section -->
    <section id="accommodations" class="landing-section">
        <div class="section-header">
            <span class="section-tag">Stays & Villas</span>
            <h2>Our Signature Accommodations</h2>
            <p>Designed for comfort, romantic getaways, and family mountain retreats.</p>
        </div>

        <div class="rooms-grid">
            <div class="room-card">
                <div class="room-tag">Most Popular</div>
                <h3>A Private Sanctuary for Two</h3>
                <p>Awaken above a sea of clouds with an intimate viewing deck, a private infinity bath, and sweeping mountain horizons tailored for unforgettable moments.</p>
                <div class="room-price">₱ 1,500 <span>/ night • Free Breakfast Included</span></div>
            </div>
            <div class="room-card">
                <div class="room-tag">Family Suite</div>
                <h3>Comfortably hosts 6 guests</h3>
                <p>Your private mountain estate. This expansive two-bedroom timber chalet blends rustic charm with modern comfort, offering the ultimate highland sanctuary for families and group getaways.</p>
                <div class="room-price">₱ 4,000 <span>/ night • Free Breakfast Included</span></div>
            </div>
            <div class="room-card">
                <div class="room-tag">Cozy Escape</div>
                <h3>Comfortably hosts 4 persons</h3>
                <p>Escape the ordinary in a premium geodesic suite built for unforgettable evenings. Features a panoramic viewing design tailored for stargazing, deep late-night conversations, and waking up wrapped in the clouds.</p>
                <div class="room-price">₱ 2,500 <span>/ night • Free Breakfast Included</span></div>
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
            <div>
                <a style="text-decoration: none; color:rgb(239, 171, 26)" href="https://www.facebook.com/profile.php?id=100045282825137" target="_blank" rel="noopener noreferrer">
                    Facebook <i class="fa-brands fa-facebook"></i>
                </a>
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

        <form id="form-login" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input" placeholder="Enter staff username" required autofocus />
            </div>
            <div class="form-group">
                <label>Password</label>
                <!-- UPDATED: Added eye icon and relative wrapper -->
                <div style="position: relative;">
                    <input type="password" name="password" id="login-password" class="form-input" placeholder="Enter password" required style="padding-right: 40px;" />
                    <i class="fa-solid fa-eye" onclick="togglePassword('login-password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                </div>
            </div>
            <button type="submit" class="btn-submit">Login</button>
        </form>

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
                <!-- UPDATED: Added eye icon and relative wrapper -->
                <div style="position: relative;">
                    <input type="password" name="password" id="reg-password" class="form-input" placeholder="At least 6 characters" required style="padding-right: 40px;" />
                    <i class="fa-solid fa-eye" onclick="togglePassword('reg-password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                </div>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <!-- UPDATED: Added eye icon and relative wrapper -->
                <div style="position: relative;">
                    <input type="password" name="password_confirmation" id="reg-password-confirm" class="form-input" placeholder="Confirm password" required style="padding-right: 40px;" />
                    <i class="fa-solid fa-eye" onclick="togglePassword('reg-password-confirm', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted);"></i>
                </div>
            </div>
            <button type="submit" class="btn-submit">Register Account</button>
        </form>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// --- NEW: Password Toggle Function ---
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
        if (registerForm) registerForm.style.display = 'none';
        tabLogin.classList.add('active');
        if (tabRegister) tabRegister.classList.remove('active');
    } else {
        loginForm.style.display = 'none';
        if (registerForm) registerForm.style.display = 'block';
        if (tabRegister) tabRegister.classList.add('active');
        tabLogin.classList.remove('active');
    }
}

// Smooth scroll for landing page nav links
document.addEventListener('DOMContentLoaded', function () {
    // Open modal if there are errors
    const modalElement = document.getElementById('authModal');
    if (modalElement && modalElement.dataset.hasErrors === 'true') {
        openAuthModal('login');
    }

    // Smooth scroll for all anchor links on the landing page
    document.querySelectorAll('.landing-nav a[href^="#"], .hero-buttons a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                const navHeight = document.querySelector('.landing-nav')?.offsetHeight || 75;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>
@endsection