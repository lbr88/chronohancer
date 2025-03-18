<?php

use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, identify and fix duplicate tags
        $this->fixDuplicateTags();

        // Then add the unique constraint
        Schema::table('tags', function (Blueprint $table) {
            // Add a unique constraint for name, user_id, and workspace_id
            $table->unique(['name', 'user_id', 'workspace_id'], 'tags_name_user_workspace_unique');
        });
    }

    /**
     * Identify and fix duplicate tags
     */
    private function fixDuplicateTags(): void
    {
        // Find duplicate tags based on name, user_id, and workspace_id
        $duplicates = DB::table('tags')
            ->select('name', 'user_id', 'workspace_id', DB::raw('COUNT(*) as count'))
            ->groupBy('name', 'user_id', 'workspace_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all tags with this name, user_id, and workspace_id
            $tags = Tag::where('name', $duplicate->name)
                ->where('user_id', $duplicate->user_id)
                ->where('workspace_id', $duplicate->workspace_id)
                ->orderBy('updated_at', 'desc')
                ->get();

            // Keep the most recently updated tag and delete the others
            $keepTag = $tags->shift(); // Remove and get the first tag (most recently updated)

            foreach ($tags as $tag) {
                // Transfer relationships to the tag we're keeping
                $this->transferRelationships($tag, $keepTag);

                // Delete the duplicate tag
                $tag->delete();
            }
        }
    }

    /**
     * Transfer relationships from one tag to another
     */
    private function transferRelationships(Tag $fromTag, Tag $toTag): void
    {
        // Transfer timer relationships
        DB::table('tag_timer')
            ->where('tag_id', $fromTag->id)
            ->update(['tag_id' => $toTag->id]);

        // Transfer time log relationships
        DB::table('tag_time_log')
            ->where('tag_id', $fromTag->id)
            ->update(['tag_id' => $toTag->id]);

        // Transfer project relationships
        DB::table('project_tag')
            ->where('tag_id', $fromTag->id)
            ->update(['tag_id' => $toTag->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_name_user_workspace_unique');
        });
    }
};
