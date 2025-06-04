<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


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

    public function updateRole(Request $request, User $user)
{
    $request->validate([
        'role' => 'required|in:customer,provider,admin,worker',
    ]);

    $user->role = $request->role;
    $user->save();

    return redirect()->back()->with('success', 'user updated succefully');
}

public function getWorkers(){
    $workers=User::where('role', 'worker')->select('id', 'name')->get();
    return $workers;
}


}
