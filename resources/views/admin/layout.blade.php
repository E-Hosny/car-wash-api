<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.dashboard') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(app()->getLocale() === 'ar')
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        /* Desktop Sidebar */
        @media (min-width: 768px) {
            .sidebar {
                width: 280px;
                height: 100vh;
                position: fixed;
                right: 0;
                top: 0;
                background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                backdrop-filter: blur(20px);
                padding: 0;
                color: white;
                overflow-y: auto;
                z-index: 1000;
                box-shadow: -5px 0 25px rgba(0, 0, 0, 0.15);
                border-left: 1px solid rgba(255, 255, 255, 0.1);
            }
            .main-content {
                margin-right: 280px;
                padding: 30px;
                min-height: 100vh;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
            }
            .mobile-nav-toggle {
                display: none;
            }
        }
        
        /* Tablet */
        @media (max-width: 991px) and (min-width: 768px) {
            .sidebar {
                width: 260px;
            }
            .main-content {
                margin-right: 260px;
                padding: 25px;
            }
        }
        
        /* Mobile */
        @media (max-width: 767px) {
            .main-content {
                padding: 15px;
                margin-right: 0;
            }
            .mobile-header {
                position: sticky;
                top: 0;
                z-index: 1001;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f093fb, #f5576c, #4facfe, #00f2fe);
            background-size: 300% 300%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .sidebar-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .sidebar-logo:hover {
            transform: scale(1.05);
        }

        .sidebar-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-top: 5px;
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 25px 10px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 10px;
        }
        
        /* Sidebar Links */
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
            margin: 0;
            position: relative;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .sidebar a i {
            width: 20px;
            margin-left: 15px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(-5px);
            padding-right: 30px;
        }

        .sidebar a:hover i {
            transform: scale(1.1);
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-right: 4px solid #f093fb;
            font-weight: 600;
        }

        .sidebar a.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #f093fb, #f5576c);
        }

        /* Mobile Offcanvas */
        .offcanvas {
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            backdrop-filter: blur(20px);
        }

        .offcanvas-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.1);
        }

        .offcanvas-title {
            color: white;
            font-weight: 700;
        }

        .offcanvas-body {
            padding: 20px 0;
        }

        .offcanvas-body a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
            margin: 0;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .offcanvas-body a i {
            width: 20px;
            margin-left: 15px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .offcanvas-body a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(-5px);
            padding-right: 30px;
        }

        .offcanvas-body a:hover i {
            transform: scale(1.1);
        }

        /* Language Toggle */
        .language-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1002;
        }

        .language-toggle .btn-group {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-radius: 25px;
            overflow: hidden;
        }

        .language-toggle .btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            color: #667eea;
            font-weight: 600;
            padding: 8px 15px;
            transition: all 0.3s ease;
        }

        .language-toggle .btn:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }

        .language-toggle .btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        @media (max-width: 767px) {
            .language-toggle {
                bottom: 10px;
                right: 10px;
            }
        }

        /* Sidebar Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Responsive Tables */
        .table-responsive {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Responsive Cards */
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        
        /* Responsive Buttons */
        .btn {
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        
        /* Responsive Modals */
        .modal-dialog {
            margin: 1rem;
        }
        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 500px;
                margin: 1.75rem auto;
            }
        }
        @media (min-width: 768px) {
            .modal-dialog {
                max-width: 600px;
            }
        }
        @media (min-width: 992px) {
            .modal-dialog {
                max-width: 800px;
            }
        }
        
        /* Responsive Forms */
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        /* Responsive Grid */
        .row {
            margin-left: -10px;
            margin-right: -10px;
        }
        .col, .col-1, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-10, .col-11, .col-12 {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Success/Error Messages */
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Language Toggle */
        .language-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1002;
        }
        @media (max-width: 767px) {
            .language-toggle {
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="d-md-none mobile-header p-3 bg-dark text-white d-flex justify-content-between align-items-center shadow-sm">
        <span class="fw-bold"><i class="bi bi-speedometer2"></i> {{ __('messages.dashboard') }}</span>
        <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-label="Toggle menu">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center">
                <img src="{{ asset('logo.png') }}" alt="شعار النظام" class="sidebar-logo" style="width: 50px; height: 50px; margin-left: 15px;">
                <div>
                    <h5 class="offcanvas-title mb-0">{{ __('messages.dashboard') }}</h5>
                    <small class="text-white-50">لوحة التحكم الإدارية</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Main Section -->
            <div class="nav-section">
                <div class="nav-section-title">الرئيسية</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> {{ __('messages.home') }}
                </a>
            </div>

            <!-- Users Section -->
            <div class="nav-section">
                <div class="nav-section-title">المستخدمين</div>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> {{ __('messages.users') }}
                </a>
            </div>

            <!-- Orders Section -->
            <div class="nav-section">
                <div class="nav-section-title">الطلبات</div>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> {{ __('messages.order_list') }}
                </a>
                <a href="{{ route('admin.orders.time-slots') }}" class="{{ request()->routeIs('admin.orders.time-slots') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> {{ __('messages.time_slots_management') }}
                </a>
            </div>

            <!-- Ratings Section -->
            <div class="nav-section">
                <div class="nav-section-title">التقييمات</div>
                <a href="{{ route('admin.ratings.index') }}" class="{{ request()->routeIs('admin.ratings.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i> التقييمات / Ratings
                </a>
            </div>

            <!-- Services Section -->
            <div class="nav-section">
                <div class="nav-section-title">الخدمات</div>
                <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i> {{ __('messages.services') }}
                </a>
            </div>

            <!-- Packages Section -->
            <div class="nav-section">
                <div class="nav-section-title">الباقات</div>
                <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> {{ __('packages.packages') }}
                </a>
                <a href="{{ route('admin.user-packages.index') }}" class="{{ request()->routeIs('admin.user-packages.*') ? 'active' : '' }}">
                    <i class="fas fa-user-check"></i> {{ __('messages.user_subscriptions') }}
                </a>
            </div>

            <!-- Notifications Section -->
            <div class="nav-section">
                <div class="nav-section-title">الإشعارات</div>
                <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') || request()->routeIs('admin.onesignal.*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i> الإشعارات / Notifications
                </a>
            </div>

            <!-- Settings Section -->
            <div class="nav-section">
                <div class="nav-section-title">الإعدادات</div>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> {{ __('messages.settings') }}
                </a>
            </div>
        </div>
    </div>

    <div class="sidebar d-none d-md-block">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <img src="{{ asset('logo.png') }}" alt="شعار النظام" class="sidebar-logo">
            <h5 class="sidebar-title">{{ __('messages.dashboard') }}</h5>
            <p class="sidebar-subtitle">لوحة التحكم الإدارية</p>
        </div>

        <!-- Sidebar Navigation -->
        <div class="sidebar-nav">
            <!-- Main Section -->
            <div class="nav-section">
                <div class="nav-section-title">الرئيسية</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i> {{ __('messages.home') }}
                </a>
            </div>

            <!-- Users Section -->
            <div class="nav-section">
                <div class="nav-section-title">المستخدمين</div>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> {{ __('messages.users') }}
                </a>
            </div>

            <!-- Orders Section -->
            <div class="nav-section">
                <div class="nav-section-title">الطلبات</div>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> {{ __('messages.order_list') }}
                </a>
                <a href="{{ route('admin.orders.time-slots') }}" class="{{ request()->routeIs('admin.orders.time-slots') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i> {{ __('messages.time_slots_management') }}
                </a>
            </div>

            <!-- Ratings Section -->
            <div class="nav-section">
                <div class="nav-section-title">التقييمات</div>
                <a href="{{ route('admin.ratings.index') }}" class="{{ request()->routeIs('admin.ratings.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i> التقييمات / Ratings
                </a>
            </div>

            <!-- Services Section -->
            <div class="nav-section">
                <div class="nav-section-title">الخدمات</div>
                <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                    <i class="fas fa-star"></i> {{ __('messages.services') }}
                </a>
            </div>

            <!-- Packages Section -->
            <div class="nav-section">
                <div class="nav-section-title">الباقات</div>
                <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> {{ __('packages.packages') }}
                </a>
                <a href="{{ route('admin.user-packages.index') }}" class="{{ request()->routeIs('admin.user-packages.*') ? 'active' : '' }}">
                    <i class="fas fa-user-check"></i> {{ __('messages.user_subscriptions') }}
                </a>
            </div>

            <!-- Notifications Section -->
            <div class="nav-section">
                <div class="nav-section-title">الإشعارات</div>
                <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') || request()->routeIs('admin.onesignal.*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i> الإشعارات / Notifications
                </a>
            </div>

            <!-- Settings Section -->
            <div class="nav-section">
                <div class="nav-section-title">الإعدادات</div>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> {{ __('messages.settings') }}
                </a>
            </div>
        </div>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <!-- Language Toggle -->
    <div class="language-toggle">
        <div class="btn-group" role="group">
            <a href="{{ route('lang.switch', 'ar') }}" class="btn {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                العربية
            </a>
            <a href="{{ route('lang.switch', 'en') }}" class="btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                English
            </a>
        </div>
    </div>

    <!-- jQuery fallback -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth animations to sidebar links
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar a, .offcanvas-body a');
            
            sidebarLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(-5px)';
                });
                
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(0)';
                    }
                });
            });

            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
    </script>

    <style>
        /* Ripple Effect */
        .btn {
            position: relative;
            overflow: hidden;
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Enhanced Mobile Header */
        .mobile-header {
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .mobile-header .btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .mobile-header .btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }
    </style>
</body>
</html>
