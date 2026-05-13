<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $sessionLocale = session('locale');

        if (in_array($sessionLocale, ['ar', 'en'], true)) {
            app()->setLocale($sessionLocale);
        } else {
            $path = trim($request->path(), '/');
            $landingUsesArabicDefault = $path === '' || $path === 'download';

            app()->setLocale($landingUsesArabicDefault ? 'ar' : config('app.locale'));
        }

        return $next($request);
    }
}
