<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('requested_event_date')->nullable()->after('requested_event_type');
            $table->index(['event_id', 'requested_event_date', 'status'], 'bookings_event_date_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_event_date_status_idx');
            $table->dropColumn('requested_event_date');
        });
    }
};
