<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class UpdateServicesSortOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all services and update their sort_order
        $services = Service::all();
        
        foreach ($services as $index => $service) {
            $service->update(['sort_order' => $index + 1]);
        }
        
        $this->command->info('Services sort order updated successfully!');
    }
}
