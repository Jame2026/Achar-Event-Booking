<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->text('image_url')->nullable()->after('body');
            $table->string('image_mime', 120)->nullable()->after('image_url');
            $table->unsignedInteger('image_size')->nullable()->after('image_mime');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'image_mime', 'image_size']);
        });
    }
};
