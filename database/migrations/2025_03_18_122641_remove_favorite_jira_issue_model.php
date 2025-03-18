<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration documents the removal of the FavoriteJiraIssue model.
     * The actual change is made by deleting the model file.
     *
     * @see App\Models\FavoriteJiraIssue
     */
    public function up(): void
    {
        // This migration is for documentation purposes only.
        // The actual change is made by deleting the FavoriteJiraIssue model file.
        // File path: app/Models/FavoriteJiraIssue.php
    }

    /**
     * Reverse the migrations.
     *
     * This would involve recreating the FavoriteJiraIssue model.
     */
    public function down(): void
    {
        // This migration is for documentation purposes only.
        // To reverse, recreate the FavoriteJiraIssue model with the following content:
        /*
        <?php

        namespace App\Models;

        use Illuminate\Database\Eloquent\Model;
        use Illuminate\Database\Eloquent\Relations\BelongsTo;

        class FavoriteJiraIssue extends Model
        {
            protected $fillable = [
                'user_id',
                'workspace_id',
                'jira_issue_id',
                'key',
                'title',
                'status',
            ];

            public function user(): BelongsTo
            {
                return $this->belongsTo(User::class);
            }

            public function workspace(): BelongsTo
            {
                return $this->belongsTo(Workspace::class);
            }

            public function getJiraUrlAttribute(): string
            {
                $baseUrl = config('jira.base_url');
                return "{$baseUrl}/browse/{$this->key}";
            }
        }
        */
    }
};
