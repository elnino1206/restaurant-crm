<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('table_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('pending');
            $table->unsignedSmallInteger('guests_count');
            $table->timestamp('booking_start');
            $table->timestamp('booking_end')->nullable();
            $table->text('comment')->nullable();
            $table->string('source', 50)->default('telegram');
            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'booking_start']);
            $table->index(['table_id', 'booking_start', 'booking_end']);
        });

        // Note: PostgreSQL uses EXCLUDE USING gist for overlap prevention.
        // On MySQL, overlap prevention is handled at the application level
        // in TableAllocator and enforced by BookingConflictException.
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
