<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->boolean('auto_accept_bookings')->default(false)->after('blocked_ranges');
            $table->unsignedSmallInteger('booking_lead_time_hours')->default(24)->after('auto_accept_bookings');
            $table->unsignedSmallInteger('buffer_minutes_between_bookings')->default(30)->after('booking_lead_time_hours');
            $table->unsignedSmallInteger('max_bookings_per_day')->nullable()->after('buffer_minutes_between_bookings');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn([
                'auto_accept_bookings',
                'booking_lead_time_hours',
                'buffer_minutes_between_bookings',
                'max_bookings_per_day',
            ]);
        });
    }
};
