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

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Desktop Sidebar */
        @media (min-width: 768px) {
            .sidebar {
                width: 250px;
                height: 100vh;
                position: fixed;
                right: 0;
                top: 0;
                background-color: #343a40;
                padding-top: 20px;
                color: white;
                overflow-y: auto;
                z-index: 1000;
            }
            .main-content {
                margin-right: 250px;
                padding: 30px;
                min-height: 100vh;
            }
            .mobile-nav-toggle {
                display: none;
            }
        }
        
        /* Tablet */
        @media (max-width: 991px) and (min-width: 768px) {
            .sidebar {
                width: 220px;
            }
            .main-content {
                margin-right: 220px;
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
            }
        }
        
        /* Sidebar Links */
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 2px 10px;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            transform: translateX(-2px);
        }
        .sidebar h5 {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        
        /* Mobile Offcanvas */
        .offcanvas-body a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 2px 0;
        }
        .offcanvas-body a:hover {
            background-color: #495057;
            transform: translateX(-2px);
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

    <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ __('messages.menu') }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-2">
            <a href="{{ route('admin.dashboard') }}" class="text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-house"></i> {{ __('messages.home') }}
            </a>
            <a href="{{ route('admin.users.index') }}" class="text-white {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> {{ __('messages.users') }}
            </a>
            <a href="{{ route('admin.orders.time-slots') }}" class="text-white {{ request()->routeIs('admin.orders.time-slots') ? 'active' : '' }}">
                <i class="bi bi-calendar-clock"></i> {{ __('messages.time_slots_management') }}
            </a>
            <a href="{{ route('admin.services.index') }}" class="text-white {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <i class="bi bi-stars"></i> {{ __('messages.services') }}
            </a>
            <a href="{{ route('admin.packages.index') }}" class="text-white {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i> {{ __('packages.packages') }}
            </a>
            <a href="{{ route('admin.user-packages.index') }}" class="text-white {{ request()->routeIs('admin.user-packages.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> {{ __('messages.user_subscriptions') }}
            </a>
            <a href="{{ route('admin.settings.index') }}" class="text-white {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> {{ __('messages.settings') }}
            </a>
            <div class="text-center mt-3 p-2">
                <div class="btn-group" role="group">
                    <a href="{{ route('lang.switch', 'ar') }}" class="btn btn-sm btn-outline-light {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                        العربية
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}" class="btn btn-sm btn-outline-light {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                        English
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar d-none d-md-block">
        <h5><i class="bi bi-speedometer2"></i> {{ __('messages.dashboard') }}</h5>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house"></i> {{ __('messages.home') }}
        </a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> {{ __('messages.users') }}
        </a>
        <a href="{{ route('admin.orders.time-slots') }}" class="{{ request()->routeIs('admin.orders.time-slots') ? 'active' : '' }}">
            <i class="bi bi-calendar-clock"></i> {{ __('messages.time_slots_management') }}
        </a>
        <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
            <i class="bi bi-stars"></i> {{ __('messages.services') }}
        </a>
        <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> {{ __('packages.packages') }}
        </a>
        <a href="{{ route('admin.user-packages.index') }}" class="{{ request()->routeIs('admin.user-packages.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> {{ __('messages.user_subscriptions') }}
        </a>
        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> {{ __('messages.settings') }}
        </a>
        <div class="text-center mt-3 p-2">
            <div class="btn-group" role="group">
                <a href="{{ route('lang.switch', 'ar') }}" class="btn btn-sm btn-outline-light {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
                    العربية
                </a>
                <a href="{{ route('lang.switch', 'en') }}" class="btn btn-sm btn-outline-light {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                    English
                </a>
            </div>
        </div>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
