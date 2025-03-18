<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration documents the removal of the favoriteJiraIssues relation
     * from the User model. The actual change is made directly to the model file.
     *
     * @see App\Models\User
     */
    public function up(): void
    {
        // This migration is for documentation purposes only.
        // The actual change is made directly to the User model by removing the favoriteJiraIssues method.
    }

    /**
     * Reverse the migrations.
     *
     * This would involve adding back the favoriteJiraIssues relation to the User model.
     */
    public function down(): void
    {
        // This migration is for documentation purposes only.
        // To reverse, add the following method back to the User model:
        /*
        public function favoriteJiraIssues(): HasMany
        {
            return $this->hasMany(FavoriteJiraIssue::class);
        }
        */
    }
};
