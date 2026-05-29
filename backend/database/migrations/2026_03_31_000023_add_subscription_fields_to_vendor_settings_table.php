<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->string('subscription_plan_code', 40)->nullable()->after('quiet_hours_end');
            $table->string('subscription_plan_name', 120)->nullable()->after('subscription_plan_code');
            $table->decimal('subscription_price_amount', 10, 2)->default(0)->after('subscription_plan_name');
            $table->unsignedSmallInteger('subscription_duration_months')->nullable()->after('subscription_price_amount');
            $table->string('subscription_status', 20)->default('inactive')->after('subscription_duration_months');
            $table->timestamp('subscription_started_at')->nullable()->after('subscription_status');
            $table->timestamp('subscription_paid_at')->nullable()->after('subscription_started_at');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_paid_at');
            $table->index('subscription_expires_at', 'vendor_settings_subscription_expires_idx');
            $table->index('subscription_status', 'vendor_settings_subscription_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropIndex('vendor_settings_subscription_expires_idx');
            $table->dropIndex('vendor_settings_subscription_status_idx');
            $table->dropColumn([
                'subscription_plan_code',
                'subscription_plan_name',
                'subscription_price_amount',
                'subscription_duration_months',
                'subscription_status',
                'subscription_started_at',
                'subscription_paid_at',
                'subscription_expires_at',
            ]);
        });
    }
};
