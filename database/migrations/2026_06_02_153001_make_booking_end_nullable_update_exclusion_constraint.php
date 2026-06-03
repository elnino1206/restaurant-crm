<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT no_overlapping_bookings');

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestampTz('booking_end')->nullable()->change();
        });

        // NULL booking_end = open-ended (treated as infinity for overlap detection)
        DB::statement("
            ALTER TABLE bookings ADD CONSTRAINT no_overlapping_bookings
            EXCLUDE USING gist (
                table_id WITH =,
                tstzrange(booking_start, COALESCE(booking_end, 'infinity'::timestamptz)) WITH &&
            ) WHERE (status IN ('pending', 'confirmed'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT no_overlapping_bookings');

        // Restore booking_end for all open-ended bookings before making it NOT NULL
        DB::statement("UPDATE bookings SET booking_end = booking_start + INTERVAL '2 hours' WHERE booking_end IS NULL");

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestampTz('booking_end')->nullable(false)->change();
        });

        DB::statement("
            ALTER TABLE bookings ADD CONSTRAINT no_overlapping_bookings
            EXCLUDE USING gist (
                table_id WITH =,
                tstzrange(booking_start, booking_end) WITH &&
            ) WHERE (status IN ('pending', 'confirmed'))
        ");
    }
};
