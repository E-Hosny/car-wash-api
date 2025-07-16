<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\Service;
use App\Models\ServicePoint;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء باقات افتراضية
        $packages = [
            [
                'name' => 'الباقة الأساسية',
                'description' => 'باقة مناسبة للاستخدام العادي مع نقاط كافية للخدمات الأساسية',
                'price' => 99.00,
                'points' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'الباقة المتقدمة',
                'description' => 'باقة شاملة مع نقاط إضافية للخدمات المتقدمة',
                'price' => 199.00,
                'points' => 250,
                'is_active' => true,
            ],
            [
                'name' => 'الباقة المميزة',
                'description' => 'باقة فاخرة مع نقاط كثيرة لجميع الخدمات',
                'price' => 299.00,
                'points' => 400,
                'is_active' => true,
            ],
        ];

        foreach ($packages as $packageData) {
            Package::create($packageData);
        }

        // إنشاء نقاط للخدمات
        $services = Service::all();
        $servicePoints = [
            'غسيل خارجي' => 20,
            'غسيل داخلي' => 30,
            'غسيل شامل' => 45,
            'تلميع السيارة' => 35,
            'تنظيف المحرك' => 25,
            'معطر السيارة' => 15,
        ];

        foreach ($services as $service) {
            $points = $servicePoints[$service->name] ?? 20; // افتراضي 20 نقطة
            ServicePoint::create([
                'service_id' => $service->id,
                'points_required' => $points,
            ]);
        }
    }
} 