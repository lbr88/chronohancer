<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Project;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Get first user as fallback
        $user = User::first();
        
        if ($user) {
            Project::whereNull('user_id')->update([
                'user_id' => $user->id
            ]);
        }
    }

    public function down(): void
    {
        // No need to undo this as the previous migration will handle dropping the column
    }
};