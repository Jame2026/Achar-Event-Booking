<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('service_name')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique('booking_id');
            $table->index(['vendor_user_id', 'last_message_at']);
            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
