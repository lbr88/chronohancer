<?php

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
        // First, ensure all time logs have descriptions from timer_descriptions
        $timeLogs = DB::table('time_logs')
            ->whereNotNull('timer_description_id')
            ->get();

        foreach ($timeLogs as $timeLog) {
            $timerDescription = DB::table('timer_descriptions')
                ->where('id', $timeLog->timer_description_id)
                ->first();

            if ($timerDescription) {
                // Update the time log with the description from timer_description
                DB::table('time_logs')
                    ->where('id', $timeLog->id)
                    ->update(['description' => $timerDescription->description]);
            }
        }

        // Remove the timer_description_id foreign key constraint
        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('timer_description_id');
        });

        // Drop the timer_descriptions table
        Schema::dropIfExists('timer_descriptions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the timer_descriptions table
        Schema::create('timer_descriptions', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->foreignId('timer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Add the timer_description_id column back to time_logs
        Schema::table('time_logs', function (Blueprint $table) {
            $table->foreignId('timer_description_id')->nullable()->after('timer_id')->constrained()->nullOnDelete();
        });

        // Migrate descriptions from time_logs back to timer_descriptions
        $timeLogs = DB::table('time_logs')
            ->whereNotNull('description')
            ->get();

        foreach ($timeLogs as $timeLog) {
            if (! empty($timeLog->description) && $timeLog->timer_id) {
                // Create a new timer description
                $timerDescriptionId = DB::table('timer_descriptions')->insertGetId([
                    'description' => $timeLog->description,
                    'timer_id' => $timeLog->timer_id,
                    'user_id' => $timeLog->user_id,
                    'workspace_id' => $timeLog->workspace_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update the time log with the new timer description ID
                DB::table('time_logs')
                    ->where('id', $timeLog->id)
                    ->update(['timer_description_id' => $timerDescriptionId]);
            }
        }
    }
};
