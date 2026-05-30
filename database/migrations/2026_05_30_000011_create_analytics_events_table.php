<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 100);
            $table->json('payload')->nullable();
            $table->timestampTz('occurred_at');
            // immutable log — no updated_at
            $table->timestampTz('created_at')->nullable();

            $table->index(['restaurant_id', 'event_type']);
            $table->index(['restaurant_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};