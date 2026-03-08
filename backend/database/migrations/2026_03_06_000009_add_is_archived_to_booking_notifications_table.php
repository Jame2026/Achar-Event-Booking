<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_notifications', function (Blueprint $table) {
            $table->boolean('is_archived')->default(false)->after('is_read');
            $table->index('is_archived');
        });
    }

    public function down(): void
    {
        Schema::table('booking_notifications', function (Blueprint $table) {
            $table->dropIndex(['is_archived']);
            $table->dropColumn('is_archived');
        });
    }
};
