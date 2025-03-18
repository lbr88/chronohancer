<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('microsoft_enabled')->default(false)->after('tempo_enabled');
            $table->text('microsoft_access_token')->nullable()->after('microsoft_enabled');
            $table->text('microsoft_refresh_token')->nullable()->after('microsoft_access_token');
            $table->timestamp('microsoft_token_expires_at')->nullable()->after('microsoft_refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'microsoft_enabled',
                'microsoft_access_token',
                'microsoft_refresh_token',
                'microsoft_token_expires_at',
            ]);
        });
    }
};
