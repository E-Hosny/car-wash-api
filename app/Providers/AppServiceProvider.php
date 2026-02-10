<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        try {
            if (\Schema::hasTable('settings')) {
                if (Setting::where('key', 'packages_enabled')->doesntExist()) {
                    Setting::setValue('packages_enabled', true);
                }
                if (Setting::where('key', 'support_whatsapp')->doesntExist()) {
                    Setting::setValue('support_whatsapp', '966542327025');
                }
                // القيم الافتراضية للحدود الجغرافية
                if (Setting::where('key', 'dubai_min_latitude')->doesntExist()) {
                    Setting::setValue('dubai_min_latitude', 24.5);
                }
                if (Setting::where('key', 'dubai_max_latitude')->doesntExist()) {
                    Setting::setValue('dubai_max_latitude', 25.5);
                }
                if (Setting::where('key', 'dubai_min_longitude')->doesntExist()) {
                    Setting::setValue('dubai_min_longitude', 54.5);
                }
                if (Setting::where('key', 'dubai_max_longitude')->doesntExist()) {
                    Setting::setValue('dubai_max_longitude', 56.0);
                }
            }
        } catch (\Throwable $e) {
            // ignore during migration
        }
    }
}
