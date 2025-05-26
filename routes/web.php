<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Middleware\SetLocale;







Route::middleware([
    StartSession::class,
    SetLocale::class,
])->group(function () {

    // ✅ تغيير اللغة
    Route::get('/lang/{locale}', function ($locale) {
        session(['locale' => $locale]);
        return back();
    })->name('lang.switch');

    // ✅ الصفحة الرئيسية
    Route::get('/', function () {
        return view('welcome'); 
    });

    // ✅ تسجيل دخول الأدمن
    Route::get('admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login.submit');

    // ✅ لوحة تحكم الأدمن وكل الصفحات الداخلية
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/services', [ServiceController::class, 'index'])->name('admin.services.index');
        Route::get('/services/create', [ServiceController::class, 'create'])->name('admin.services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('admin.services.store');
        Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
        Route::post('/services/{id}/update', [ServiceController::class, 'update'])->name('admin.services.update');
        Route::post('/services/{id}/delete', [ServiceController::class, 'destroy'])->name('admin.services.delete');

        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/customers', [UserController::class, 'customers'])->name('admin.users.customers');
        Route::get('/users/providers', [UserController::class, 'providers'])->name('admin.users.providers');

        Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    });

});
