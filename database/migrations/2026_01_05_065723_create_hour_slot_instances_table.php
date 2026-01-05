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
        Schema::create('hour_slot_instances', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // التاريخ
            $table->integer('hour'); // الساعة (10-23)
            $table->integer('slot_index'); // فهرس الـ slot (1, 2, 3...)
            $table->enum('status', ['available', 'disabled', 'booked'])->default('available'); // حالة الـ slot
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null'); // الطلب المرتبط (إذا كان محجوز)
            $table->timestamps();
            
            // فهرس مركب لضمان عدم تكرار الـ slot في نفس التاريخ والساعة
            $table->unique(['date', 'hour', 'slot_index']);
            $table->index(['date', 'hour', 'status']);
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hour_slot_instances');
    }
};
