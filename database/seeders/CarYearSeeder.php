<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarYear;

class CarYearSeeder extends Seeder
{
    public function run(): void
    {
        $start = 2000;
        $end = now()->year;

        for ($year = $start; $year <= $end; $year++) {
            CarYear::create(['year' => $year]);
        }
    }
}
