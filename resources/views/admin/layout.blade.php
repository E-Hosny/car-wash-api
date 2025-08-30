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
        }
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
            }
            .main-content {
                margin-right: 250px;
                padding: 30px;
            }
            .mobile-nav-toggle {
                display: none;
            }
        }
        @media (max-width: 767px) {
            .main-content {
                padding: 20px;
            }
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
        }
        .sidebar h5 {
            text-align: center;
            margin-bottom: 30px;
        }
        .offcanvas-body a:hover {
            background-color: #495057;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <div class="d-md-none p-2 bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-speedometer2"></i> {{ __('messages.dashboard') }}</span>
        <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
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
            <a href="{{ route('admin.orders.index') }}" class="text-white {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i> {{ __('messages.orders') }}
            </a>
            <a href="{{ route('admin.services.index') }}" class="text-white {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <i class="bi bi-stars"></i> {{ __('messages.services') }}
            </a>
            <a href="{{ route('admin.packages.index') }}" class="text-white {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i> {{ __('packages.packages') }}
            </a>
            <a href="{{ route('admin.user-packages.index') }}" class="text-white {{ request()->routeIs('admin.user-packages.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> User Subscriptions
            </a>
            <a href="{{ route('admin.settings.index') }}" class="text-white {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> {{ __('messages.settings') }}
            </a>
            <div class="text-center mt-2">
                <a href="{{ route('lang.switch', 'ar') }}">العربية</a> |
                <a href="{{ route('lang.switch', 'en') }}">English</a>
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
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> {{ __('messages.orders') }}
        </a>
        <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
            <i class="bi bi-stars"></i> {{ __('messages.services') }}
        </a>
        <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
            <i class="bi bi-box"></i> {{ __('packages.packages') }}
        </a>
        <a href="{{ route('admin.user-packages.index') }}" class="{{ request()->routeIs('admin.user-packages.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> User Subscriptions
        </a>
        <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> {{ __('messages.settings') }}
        </a>
        <div class="text-center mt-2">
            <a href="{{ route('lang.switch', 'ar') }}">العربية</a> |
            <a href="{{ route('lang.switch', 'en') }}">English</a>
        </div>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
