<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class ConfigController extends Controller
{
    public function appConfig()
    {
        $packagesEnabled = (bool) (Setting::getValue('packages_enabled', '1') === '1' || Setting::getValue('packages_enabled', true));

        return response()->json([
            'success' => true,
            'data' => [
                'packages_enabled' => $packagesEnabled,
            ],
        ]);
    }
} 