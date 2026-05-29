<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->json('blocked_ranges')->nullable()->after('blocked_dates');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_settings', function (Blueprint $table) {
            $table->dropColumn('blocked_ranges');
        });
    }
};
