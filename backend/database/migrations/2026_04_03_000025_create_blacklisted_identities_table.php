<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklisted_identities', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name')->nullable();
            $table->string('subject_role', 20)->nullable()->index();
            $table->string('blocked_email')->nullable()->index();
            $table->string('blocked_phone')->nullable()->index();
            $table->text('reason')->nullable();
            $table->foreignId('blacklisted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('blacklisted_at')->nullable()->index();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklisted_identities');
    }
};
