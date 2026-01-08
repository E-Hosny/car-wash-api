<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('geographical_bounds', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم/وصف الحد
            $table->decimal('min_latitude', 10, 6); // الحد الأدنى لخط العرض
            $table->decimal('max_latitude', 10, 6); // الحد الأقصى لخط العرض
            $table->decimal('min_longitude', 10, 6); // الحد الأدنى لخط الطول
            $table->decimal('max_longitude', 10, 6); // الحد الأقصى لخط الطول
            $table->timestamps();
            
            // إضافة indexes لتحسين الأداء
            $table->index(['min_latitude', 'max_latitude']);
            $table->index(['min_longitude', 'max_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geographical_bounds');
    }
};
