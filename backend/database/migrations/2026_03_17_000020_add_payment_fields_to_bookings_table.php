<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_status', 30)->default('pending')->after('status');
            $table->string('payment_method', 30)->nullable()->after('payment_status');
            $table->string('payment_reference', 120)->nullable()->after('payment_method');
            $table->string('payment_token', 80)->nullable()->after('payment_reference');
            $table->timestamp('paid_at')->nullable()->after('payment_token');

            $table->index('payment_status', 'bookings_payment_status_idx');
            $table->unique('payment_token', 'bookings_payment_token_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('bookings_payment_token_unique');
            $table->dropIndex('bookings_payment_status_idx');
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'payment_reference',
                'payment_token',
                'paid_at',
            ]);
        });
    }
};
