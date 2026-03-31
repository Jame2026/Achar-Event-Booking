<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->unsignedInteger('subscription_service_limit')->nullable()->after('subscription_duration_months');
            $table->unsignedInteger('subscription_package_limit')->nullable()->after('subscription_service_limit');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_service_limit',
                'subscription_package_limit',
            ]);
        });
    }
};
