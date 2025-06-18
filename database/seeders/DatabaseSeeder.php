<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\CarMakeSeeder;
use Database\Seeders\CarModelSeeder;
use Database\Seeders\CarYearSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

      $this->call([
        CarFullSeeder::class,
        CarYearSeeder::class,
    ]);


  
        
    }
}
