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
            $table->foreignUuid('schedule_day_id')->constrained('schedule_days')->onDelete('cascade');
            $table->foreignUuid('schedule_hour_id')->constrained('schedule_hours')->onDelete('cascade')->index();

            $table->timestamps();

            $table->primary(['schedule_day_id', 'schedule_hour_id']);
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
