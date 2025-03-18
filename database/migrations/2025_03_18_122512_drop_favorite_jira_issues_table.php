<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop the favorite_jira_issues table as we're removing the Jira favorites functionality.
     */
    public function up(): void
    {
        Schema::dropIfExists('favorite_jira_issues');
    }

    /**
     * Reverse the migrations.
     *
     * Recreate the favorite_jira_issues table if the migration is rolled back.
     */
    public function down(): void
    {
        Schema::create('favorite_jira_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('jira_issue_id');
            $table->string('key');
            $table->string('title');
            $table->string('status')->nullable();
            $table->timestamps();

            // Ensure users can't favorite the same issue multiple times in the same workspace
            $table->unique(['user_id', 'workspace_id', 'jira_issue_id']);
        });
    }
};
