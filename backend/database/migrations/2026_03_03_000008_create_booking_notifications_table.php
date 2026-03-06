<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_type', 20);
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->string('kind', 40)->default('booking_created');
            $table->string('title', 255);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['recipient_user_id', 'recipient_type']);
            $table->index(['recipient_email', 'recipient_type']);
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_notifications');
    }
};
