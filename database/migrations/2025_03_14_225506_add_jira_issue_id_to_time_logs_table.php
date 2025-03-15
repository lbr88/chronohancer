<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            $table->string('jira_issue_id')->nullable()->after('workspace_id');
            $table->string('jira_issue_key')->nullable()->after('jira_issue_id');
        });
    }

    public function down(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropColumn(['jira_issue_id', 'jira_issue_key']);
        });
    }
};
