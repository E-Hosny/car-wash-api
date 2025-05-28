<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarModel; // أو Model حسب الاسم

class CarModelSeeder extends Seeder
{
    public function run(): void
    {
        // بيانات افتراضية
        $models = ['Corolla', 'Camry', 'Civic', 'Altima', 'Sonata'];

        foreach ($models as $model) {
            CarModel::create(['name' => $model, 'brand_id' => 1]); // عدل الـ brand_id حسب الحاجة
        }
    }
}
