<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jira_access_token')->nullable();
            $table->string('jira_refresh_token')->nullable();
            $table->string('jira_token_expires_at')->nullable();
            $table->string('jira_cloud_id')->nullable();
            $table->string('jira_site_url')->nullable();
            $table->boolean('jira_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'jira_access_token',
                'jira_refresh_token',
                'jira_token_expires_at',
                'jira_cloud_id',
                'jira_site_url',
                'jira_enabled',
            ]);
        });
    }
};
