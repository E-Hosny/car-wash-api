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
        Schema::create('slot_status_history', function (Blueprint $table) {
            $table->id();
            $table->enum('slot_type', ['hour_slot_instance', 'daily_time_slot']);
            $table->date('date');
            $table->integer('hour');
            $table->integer('slot_index')->nullable();
            $table->string('previous_status');
            $table->string('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_status_history');
    }
};
