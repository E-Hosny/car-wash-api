<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Brand;
use App\Models\CarModel;

class CarFullSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('data/car_data.json'));
        $data = json_decode($json, true);

        foreach ($data as $car) {
            $brand = Brand::create(['name' => $car['brand']]);

            foreach ($car['models'] as $modelName) {
                CarModel::create([
                    'name' => $modelName,
                    'brand_id' => $brand->id
                ]);
            }
        }
    }
}
