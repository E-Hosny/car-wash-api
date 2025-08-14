<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        try {
            if (\Schema::hasTable('settings')) {
                if (Setting::where('key', 'packages_enabled')->doesntExist()) {
                    Setting::setValue('packages_enabled', true);
                }
            }
        } catch (\Throwable $e) {
            // ignore during migration
        }
    }
}
