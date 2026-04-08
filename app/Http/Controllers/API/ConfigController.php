<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class ConfigController extends Controller
{
    private const DEFAULT_CAR_TYPE_RULES = [
        ['key' => 'sedan', 'label_en' => 'Sedan', 'label_ar' => 'سيدان', 'percentage' => 0],
        ['key' => '4x4_5', 'label_en' => '4*4 (5 seats)', 'label_ar' => '4*4 (5 مقاعد)', 'percentage' => 15],
        ['key' => '4x4_7', 'label_en' => '4*4 (7 seats)', 'label_ar' => '4*4 (7 مقاعد)', 'percentage' => 20],
        ['key' => 'carnival', 'label_en' => 'Carnival', 'label_ar' => 'كارنفال', 'percentage' => 25],
    ];

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
        $carTypePricingRules = collect(Setting::getValue('car_type_pricing_rules', self::DEFAULT_CAR_TYPE_RULES))
            ->filter(fn ($item) => is_array($item) && !empty($item['key']) && isset($item['percentage']))
            ->map(function ($item) {
                return [
                    'key' => (string) $item['key'],
                    'label_en' => (string) ($item['label_en'] ?? $item['label'] ?? $item['key']),
                    'label_ar' => (string) ($item['label_ar'] ?? $item['label'] ?? $item['key']),
                    'percentage' => (float) $item['percentage'],
                ];
            })
            ->values()
            ->toArray();

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
                'car_type_pricing_rules' => $carTypePricingRules,
            ],
        ]);
    }
} 