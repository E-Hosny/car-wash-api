<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if order_cars table exists
        if (!Schema::hasTable('order_cars')) {
            Schema::create('order_cars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('car_id')->constrained()->onDelete('cascade');
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->integer('points_used')->default(0);
                $table->timestamps();
            });
            echo "Created order_cars table\n";
        } else {
            echo "order_cars table already exists\n";
        }

        // Check if order_car_service table exists
        if (!Schema::hasTable('order_car_service')) {
            Schema::create('order_car_service', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_car_id')->constrained()->onDelete('cascade');
                $table->foreignId('service_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
            echo "Created order_car_service table\n";
        } else {
            echo "order_car_service table already exists\n";
        }

        // Check if services column exists in package_orders table
        if (Schema::hasTable('package_orders')) {
            if (!Schema::hasColumn('package_orders', 'services')) {
                Schema::table('package_orders', function (Blueprint $table) {
                    $table->json('services')->nullable()->after('points_used');
                });
                echo "Added services column to package_orders table\n";
            } else {
                echo "services column already exists in package_orders table\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is for checking and fixing, so down() is minimal
        echo "Migration check completed\n";
    }
}; 