<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(['vendor_id', 'starts_at'], 'events_vendor_starts_at_idx');
            $table->index(['is_active', 'starts_at'], 'events_active_starts_at_idx');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['customer_email', 'created_at'], 'bookings_customer_email_created_at_idx');
            $table->index(['status', 'created_at'], 'bookings_status_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_vendor_starts_at_idx');
            $table->dropIndex('events_active_starts_at_idx');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_customer_email_created_at_idx');
            $table->dropIndex('bookings_status_created_at_idx');
        });
    }
};
