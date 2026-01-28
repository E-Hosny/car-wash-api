<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\UserPackageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\OneSignalTestController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\API\QrCodeController;
use App\Http\Controllers\SupportController;
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
        return view('download'); 
    });

    // ✅ QR Code redirect إلى Google Play
    Route::get('/qr1', [QrCodeController::class, 'qr1'])->name('qr.redirect');

    // ✅ صفحة شروط الخصوصية
    Route::get('/terms-of-privacy', function () {
        return view('terms-of-privacy');
    })->name('terms.of.privacy');

    // ✅ صفحة الدعم
    Route::get('/support', [SupportController::class, 'index'])->name('support');
    Route::post('/support/ticket', [SupportController::class, 'submitTicket'])->name('support.ticket');

    // ✅ صفحة حذف الحساب
    Route::get('/delete-account', [App\Http\Controllers\DeleteAccountController::class, 'show'])->name('delete.account');
    Route::post('/delete-account', [App\Http\Controllers\DeleteAccountController::class, 'requestDeletion'])->name('delete.account.request');
    Route::get('/delete-account/confirm/{token}', [App\Http\Controllers\DeleteAccountController::class, 'confirmDeletion'])->name('delete.account.confirm');

    // ✅ صفحة تحميل التطبيق
    Route::get('/download', function () {
        return view('download');
    })->name('download');

    // ✅ تسجيل دخول الأدمن
    Route::get('admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
    
    // ✅ Route باسم 'login' للتوافق مع Laravel's default authentication redirect
    Route::get('login', function () {
        return redirect()->route('admin.login');
    })->name('login');

    // ✅ لوحة تحكم الأدمن وكل الصفحات الداخلية
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/services', [ServiceController::class, 'index'])->name('admin.services.index');
        Route::get('/services/create', [ServiceController::class, 'create'])->name('admin.services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('admin.services.store');
        Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
        Route::post('/services/{id}/update', [ServiceController::class, 'update'])->name('admin.services.update');
        Route::post('/services/{id}/delete', [ServiceController::class, 'destroy'])->name('admin.services.delete');
        Route::post('/services/update-order', [ServiceController::class, 'updateOrder'])->name('admin.services.update-order');
        Route::post('/services/{id}/move-up', [ServiceController::class, 'moveUp'])->name('admin.services.move-up');
        Route::post('/services/{id}/move-down', [ServiceController::class, 'moveDown'])->name('admin.services.move-down');

        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/customers', [UserController::class, 'customers'])->name('admin.users.customers');
        Route::get('/users/providers', [UserController::class, 'providers'])->name('admin.users.providers');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');

        Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/orders/time-slots', [OrderController::class, 'timeSlots'])->name('admin.orders.time-slots');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::get('/orders/{order}/status', [OrderController::class, 'getStatus'])->name('admin.orders.status');
        Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
        
        // Time Slots Management
        Route::post('/time-slots/{hour}/toggle', [OrderController::class, 'toggleTimeSlot'])->name('admin.time-slots.toggle');
        Route::post('/time-slots/{hour}/set-status', [OrderController::class, 'setTimeSlotStatus'])->name('admin.time-slots.set-status');
        Route::get('/time-slots/status', [OrderController::class, 'getTimeSlotsStatus'])->name('admin.time-slots.status');

        // Packages routes
        Route::get('/packages', [PackageController::class, 'index'])->name('admin.packages.index');
        Route::get('/packages/create', [PackageController::class, 'create'])->name('admin.packages.create');
        Route::post('/packages', [PackageController::class, 'store'])->name('admin.packages.store');
        Route::get('/packages/{id}/edit', [PackageController::class, 'edit'])->name('admin.packages.edit');
        Route::post('/packages/{id}/update', [PackageController::class, 'update'])->name('admin.packages.update');
        Route::post('/packages/{id}/delete', [PackageController::class, 'destroy'])->name('admin.packages.delete');
        Route::post('/packages/{id}/toggle-status', [PackageController::class, 'toggleStatus'])->name('admin.packages.toggle-status');
        Route::get('/packages/statistics', [PackageController::class, 'statistics'])->name('admin.packages.statistics');

        // User Package Subscriptions routes
        Route::get('/user-packages', [UserPackageController::class, 'index'])->name('admin.user-packages.index');
        Route::get('/user-packages/create', [UserPackageController::class, 'create'])->name('admin.user-packages.create');
        Route::post('/user-packages', [UserPackageController::class, 'store'])->name('admin.user-packages.store');
        Route::get('/user-packages/{id}', [UserPackageController::class, 'show'])->name('admin.user-packages.show');
        Route::get('/user-packages/{id}/edit', [UserPackageController::class, 'edit'])->name('admin.user-packages.edit');
        Route::put('/user-packages/{id}', [UserPackageController::class, 'update'])->name('admin.user-packages.update');
        Route::delete('/user-packages/{id}', [UserPackageController::class, 'destroy'])->name('admin.user-packages.destroy');
        Route::post('/user-packages/{id}/activate', [UserPackageController::class, 'activate'])->name('admin.user-packages.activate');
        Route::post('/user-packages/{id}/deactivate', [UserPackageController::class, 'deactivate'])->name('admin.user-packages.deactivate');
        Route::post('/user-packages/{id}/extend', [UserPackageController::class, 'extend'])->name('admin.user-packages.extend');
        Route::get('/user-packages/filter', [UserPackageController::class, 'filter'])->name('admin.user-packages.filter');

        // Settings
        Route::get('/settings', [SettingsController::class, 'edit'])->name('admin.settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
        
        // Geographical Bounds Management
        Route::post('/settings/bounds', [SettingsController::class, 'boundsStore'])->name('admin.settings.bounds.store');
        Route::post('/settings/bounds/{id}/update', [SettingsController::class, 'boundsUpdate'])->name('admin.settings.bounds.update');
        Route::delete('/settings/bounds/{id}', [SettingsController::class, 'boundsDestroy'])->name('admin.settings.bounds.destroy');

        // Notifications
        Route::get('/notifications', [OneSignalTestController::class, 'index'])->name('admin.notifications.index');
        Route::post('/onesignal/test-send', [OneSignalTestController::class, 'sendTest'])->name('admin.onesignal.test.send');
        Route::post('/onesignal/send-to-player', [OneSignalTestController::class, 'sendToPlayer'])->name('admin.onesignal.send.to.player');
        Route::post('/onesignal/send-to-user', [OneSignalTestController::class, 'sendToUser'])->name('admin.onesignal.send.to.user');
        Route::get('/onesignal/get-players', [OneSignalTestController::class, 'getPlayers'])->name('admin.onesignal.get.players');
        Route::post('/notifications/order-payment-settings', [OneSignalTestController::class, 'updateOrderPaymentSettings'])->name('admin.notifications.order-payment-settings');
        Route::post('/notifications/order-completion-rating-settings', [OneSignalTestController::class, 'updateOrderCompletionRatingSettings'])->name('admin.notifications.order-completion-rating-settings');

        // Ratings
        Route::get('/ratings', [RatingController::class, 'index'])->name('admin.ratings.index');
    });

});
