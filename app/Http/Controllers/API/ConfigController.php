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

        $bannerPath = (string) Setting::getValue('home_banner_image_url', '');
        $bannerImageUrl = $bannerPath !== ''
            ? (str_starts_with($bannerPath, 'http') ? $bannerPath : rtrim(config('app.url'), '/') . '/storage/' . ltrim($bannerPath, '/'))
            : '';
        $homeBannerLinkType = (string) Setting::getValue('home_banner_link_type', 'none');
        $homeBannerLinkExternalUrl = (string) Setting::getValue('home_banner_link_external_url', '');

        return response()->json([
            'success' => true,
            'data' => [
                'packages_enabled' => $packagesEnabled,
                'min_android_version' => $minAndroidVersion,
                'min_ios_version' => $minIosVersion,
                'android_store_url' => $androidStoreUrl,
                'ios_store_url' => $iosStoreUrl,
                'home_banner_image_url' => $bannerImageUrl,
                'home_banner_link_type' => $homeBannerLinkType,
                'home_banner_link_external_url' => $homeBannerLinkExternalUrl,
            ],
        ]);
    }
} 