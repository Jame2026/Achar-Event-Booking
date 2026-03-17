<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('service_mode', 20)->default('overall')->after('qr_code_url');
        });

        DB::table('events')
            ->whereNotNull('packages')
            ->update(['service_mode' => 'package']);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('service_mode');
        });
    }
};
