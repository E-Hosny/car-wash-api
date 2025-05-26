<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function customers()
    {
        $users = User::where('role', 'customer')->latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function providers()
    {
        $users = User::where('role', 'provider')->latest()->get();
        return view('admin.users.index', compact('users'));
    }
}
