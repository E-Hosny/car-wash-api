<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class ConfigController extends Controller
{
    public function appConfig()
    {
        $packagesEnabled = (bool) (Setting::getValue('packages_enabled', '1') === '1' || Setting::getValue('packages_enabled', true));
        $minAndroidVersion = (string) Setting::getValue('min_android_version', '');
        $minIosVersion = (string) Setting::getValue('min_ios_version', '');
        $androidStoreUrl = (string) Setting::getValue('android_store_url', '');
        $iosStoreUrl = (string) Setting::getValue('ios_store_url', '');

        return response()->json([
            'success' => true,
            'data' => [
                'packages_enabled' => $packagesEnabled,
                'min_android_version' => $minAndroidVersion,
                'min_ios_version' => $minIosVersion,
                'android_store_url' => $androidStoreUrl,
                'ios_store_url' => $iosStoreUrl,
            ],
        ]);
    }
} 