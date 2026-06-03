<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slot_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->constrained()->cascadeOnDelete();
            // 0=Monday … 6=Sunday
            $table->unsignedTinyInteger('day_of_week');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            // slot grid step in minutes (e.g. 30 → 12:00, 12:30, 13:00…)
            $table->unsignedSmallInteger('slot_duration')->default(30);
            // default booking length in minutes (0 = until closing)
            $table->unsignedSmallInteger('booking_duration')->default(0);
            $table->boolean('is_day_off')->default(false);
            $table->timestampsTz();

            $table->unique(['restaurant_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slot_configs');
    }
};
