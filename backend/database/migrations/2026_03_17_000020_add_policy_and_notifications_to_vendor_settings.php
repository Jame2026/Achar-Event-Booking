<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->decimal('deposit_percent', 5, 2)->default(20.0)->after('max_bookings_per_day');
            $table->unsignedSmallInteger('cancellation_policy_hours')->default(72)->after('deposit_percent');
            $table->unsignedSmallInteger('reschedule_policy_hours')->default(48)->after('cancellation_policy_hours');
            $table->boolean('notify_email')->default(true)->after('reschedule_policy_hours');
            $table->boolean('notify_sms')->default(false)->after('notify_email');
            $table->char('quiet_hours_start', 5)->nullable()->after('notify_sms');
            $table->char('quiet_hours_end', 5)->nullable()->after('quiet_hours_start');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn([
                'deposit_percent',
                'cancellation_policy_hours',
                'reschedule_policy_hours',
                'notify_email',
                'notify_sms',
                'quiet_hours_start',
                'quiet_hours_end',
            ]);
        });
    }
};
