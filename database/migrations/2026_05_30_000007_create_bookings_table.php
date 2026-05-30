<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Required for the EXCLUDE USING gist constraint
        DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('table_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
            // staff member who created the booking (nullable — bot bookings have no staff)
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('pending');
            $table->unsignedSmallInteger('guests_count');
            $table->timestampTz('booking_start');
            $table->timestampTz('booking_end');
            $table->text('comment')->nullable();
            // telegram | web | phone | walk_in
            $table->string('source', 50)->default('telegram');
            $table->timestampsTz();

            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'booking_start']);
            $table->index(['table_id', 'booking_start', 'booking_end']);
        });

        // Prevents double-booking the same table for overlapping time ranges
        DB::statement("
            ALTER TABLE bookings
            ADD CONSTRAINT no_overlapping_bookings
            EXCLUDE USING gist (
                table_id WITH =,
                tstzrange(booking_start, booking_end) WITH &&
            ) WHERE (status IN ('pending', 'confirmed'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};