<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tempo_access_token')->nullable();
            $table->string('tempo_refresh_token')->nullable();
            $table->timestamp('tempo_token_expires_at')->nullable();
            $table->boolean('tempo_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tempo_access_token',
                'tempo_refresh_token',
                'tempo_token_expires_at',
                'tempo_enabled',
            ]);
        });
    }
};
