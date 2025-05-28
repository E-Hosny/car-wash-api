<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class CarMakeSeeder extends Seeder
{
    public function run(): void
    {
        $makes = ['Toyota', 'BMW', 'Mercedes', 'Hyundai', 'Nissan'];

        foreach ($makes as $make) {
            Brand::create(['name' => $make]);
        }
    }
}
