<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Service;

class DashboardController extends Controller
{
    public function index()
    {
        // Get users for OneSignal push selection
        $users = User::select('id', 'name', 'email', 'phone', 'role')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_providers' => User::where('role', 'provider')->count(),
            'total_orders' => Order::count(),
            'total_services' => Service::count(),
            'onesignal_players' => session('onesignal_players', []),
            'users' => $users,
        ]);
    }
}
