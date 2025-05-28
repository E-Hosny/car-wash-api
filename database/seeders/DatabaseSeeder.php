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
            CarMakeSeeder::class,
            CarModelSeeder::class,
            CarYearSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0500000000', // ← أضف رقم هاتف تجريبي
            'password' => bcrypt('password'), // مهم لضمان الدخول لو هتستخدمه
        ]);
        
    }
}
