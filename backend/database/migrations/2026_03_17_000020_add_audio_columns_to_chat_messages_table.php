<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->text('audio_url')->nullable()->after('body');
            $table->string('audio_mime', 120)->nullable()->after('audio_url');
            $table->unsignedSmallInteger('audio_duration')->nullable()->after('audio_mime');
            $table->unsignedInteger('audio_size')->nullable()->after('audio_duration');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['audio_url', 'audio_mime', 'audio_duration', 'audio_size']);
        });
    }
};
