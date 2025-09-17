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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('hour')->unique(); // 10-23
            $table->string('label'); // "10:00 AM", "11:00 AM", etc.
            $table->boolean('is_available')->default(true); // true = ON, false = OFF
            $table->text('notes')->nullable(); // ملاحظات الأدمن
            $table->timestamps();
        });

        // إدراج الساعات الافتراضية
        $this->insertDefaultTimeSlots();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }

    /**
     * إدراج الساعات الافتراضية
     */
    private function insertDefaultTimeSlots(): void
    {
        $timeSlots = [];
        
        for ($hour = 10; $hour <= 23; $hour++) {
            $period = $hour < 12 ? 'AM' : 'PM';
            $displayHour = $hour > 12 ? $hour - 12 : $hour;
            if ($hour == 12) $displayHour = 12;
            
            $timeSlots[] = [
                'hour' => $hour,
                'label' => $displayHour . ':00 ' . $period,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('time_slots')->insert($timeSlots);
    }
};