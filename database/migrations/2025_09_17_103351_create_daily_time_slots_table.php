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
        Schema::create('daily_time_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // التاريخ المحدد
            $table->integer('hour'); // الساعة (10-23)
            $table->boolean('is_available')->default(true); // true = متاح, false = غير متاح
            $table->text('notes')->nullable(); // ملاحظات الأدمن
            $table->timestamps();
            
            // فهرس مركب لضمان عدم تكرار الساعة في نفس التاريخ
            $table->unique(['date', 'hour']);
            $table->index(['date', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_time_slots');
    }
};