<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Package;
use App\Models\UserPackage; // تأكد من وجود هذا الموديل أو أنشئه إذا لم يكن موجوداً

class UserPackageController extends Controller
{
    // عرض جميع اشتراكات المستخدمين في الباقات
    public function index()
    {
        $userPackages = UserPackage::with(['user', 'package'])->latest()->paginate(20);
        $users = User::all();
        $packages = Package::all();
        return view('admin.user-packages.index', compact('userPackages', 'users', 'packages'));
    }

    // عرض صفحة إنشاء اشتراك جديد
    public function create()
    {
        $users = User::all();
        $packages = Package::all();
        return view('admin.user-packages.create', compact('users', 'packages'));
    }

    // حفظ اشتراك جديد
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,inactive',
            'expires_at' => 'nullable|date',
        ]);
        $userPackage = UserPackage::create($request->all());
        return redirect()->route('admin.user-packages.index')->with('success', 'تم إضافة الاشتراك بنجاح');
    }

    // عرض تفاصيل اشتراك معين
    public function show($id)
    {
        $userPackage = UserPackage::with(['user', 'package'])->findOrFail($id);
        return view('admin.user-packages.show', compact('userPackage'));
    }

    // عرض صفحة تعديل اشتراك
    public function edit($id)
    {
        $userPackage = UserPackage::findOrFail($id);
        $users = User::all();
        $packages = Package::all();
        return view('admin.user-packages.edit', compact('userPackage', 'users', 'packages'));
    }

    // تحديث بيانات اشتراك
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'status' => 'required|in:active,inactive',
            'expires_at' => 'nullable|date',
        ]);
        $userPackage = UserPackage::findOrFail($id);
        $userPackage->update($request->all());
        return redirect()->route('admin.user-packages.index')->with('success', 'تم تحديث الاشتراك بنجاح');
    }

    // حذف اشتراك
    public function destroy($id)
    {
        $userPackage = UserPackage::findOrFail($id);
        $userPackage->delete();
        return redirect()->route('admin.user-packages.index')->with('success', 'تم حذف الاشتراك بنجاح');
    }

    // تفعيل اشتراك
    public function activate($id)
    {
        $userPackage = UserPackage::findOrFail($id);
        $userPackage->update(['status' => 'active']);
        return redirect()->back()->with('success', 'تم تفعيل الاشتراك بنجاح');
    }

    // إلغاء تفعيل اشتراك
    public function deactivate($id)
    {
        $userPackage = UserPackage::findOrFail($id);
        $userPackage->update(['status' => 'inactive']);
        return redirect()->back()->with('success', 'تم إلغاء تفعيل الاشتراك بنجاح');
    }

    // تمديد اشتراك لمدة سنة
    public function extend($id)
    {
        $userPackage = UserPackage::findOrFail($id);
        $newExpiryDate = $userPackage->expires_at ? $userPackage->expires_at->addYear() : now()->addYear();
        $userPackage->update([
            'expires_at' => $newExpiryDate,
            'status' => 'active'
        ]);
        return redirect()->back()->with('success', 'تم تمديد الاشتراك لمدة سنة بنجاح');
    }

    // تصفية النتائج
    public function filter(Request $request)
    {
        $query = UserPackage::with(['user', 'package']);

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'expired') {
                $query->where('expires_at', '<', now()->toDateString());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $userPackages = $query->latest()->paginate(20);
        $users = User::all();
        $packages = Package::all();

        return view('admin.user-packages.index', compact('userPackages', 'users', 'packages'));
    }
} 