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
        Schema::create('schedule_day_hour', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('schedule_day_id');
            $table->foreign('schedule_day_id')->references('id')->on('schedule_days')->cascadeOnDelete();

            $table->uuid('schedule_hour_id')->index();
            $table->foreign('schedule_hour_id')->references('id')->on('schedule_hours')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['schedule_day_id', 'schedule_hour_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_day_hour');
    }
};
