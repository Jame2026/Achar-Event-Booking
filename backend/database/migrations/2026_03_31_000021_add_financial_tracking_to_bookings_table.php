<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('deposit_percent', 5, 2)->default(30.0)->after('total_amount');
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('deposit_percent');
            $table->decimal('service_fee_amount', 10, 2)->default(0)->after('deposit_amount');
            $table->decimal('balance_due_amount', 10, 2)->default(0)->after('service_fee_amount');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('balance_due_amount');
            $table->decimal('vendor_penalty_amount', 10, 2)->default(0)->after('refund_amount');
            $table->decimal('customer_compensation_amount', 10, 2)->default(0)->after('vendor_penalty_amount');
            $table->decimal('admin_compensation_amount', 10, 2)->default(0)->after('customer_compensation_amount');
            $table->timestamp('vendor_cancellation_deadline_at')->nullable()->after('paid_at');
            $table->timestamp('cancelled_at')->nullable()->after('vendor_cancellation_deadline_at');
            $table->string('cancellation_actor', 30)->nullable()->after('cancelled_at');

            $table->index('vendor_cancellation_deadline_at', 'bookings_vendor_cancel_deadline_idx');
            $table->index(['status', 'payment_status'], 'bookings_status_payment_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_vendor_cancel_deadline_idx');
            $table->dropIndex('bookings_status_payment_idx');
            $table->dropColumn([
                'deposit_percent',
                'deposit_amount',
                'service_fee_amount',
                'balance_due_amount',
                'refund_amount',
                'vendor_penalty_amount',
                'customer_compensation_amount',
                'admin_compensation_amount',
                'vendor_cancellation_deadline_at',
                'cancelled_at',
                'cancellation_actor',
            ]);
        });
    }
};
