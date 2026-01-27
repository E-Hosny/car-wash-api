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
        Schema::table('app_ratings', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('user_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unique(['user_id', 'order_id'], 'user_order_rating_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_ratings', function (Blueprint $table) {
            $table->dropUnique('user_order_rating_unique');
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
