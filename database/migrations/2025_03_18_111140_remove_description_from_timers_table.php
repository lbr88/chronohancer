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
        // First, migrate existing descriptions to the new table
        $timers = DB::table('timers')->whereNotNull('description')->get();

        foreach ($timers as $timer) {
            if (! empty($timer->description)) {
                DB::table('timer_descriptions')->insert([
                    'description' => $timer->description,
                    'timer_id' => $timer->id,
                    'user_id' => $timer->user_id,
                    'workspace_id' => $timer->workspace_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Then remove the description column from timers table
        Schema::table('timers', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add the description column back
        Schema::table('timers', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        // Migrate the most recent description back to the timers table
        $descriptions = DB::table('timer_descriptions')
            ->select('timer_id', 'description')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('timer_id');

        foreach ($descriptions as $timerId => $timerDescriptions) {
            // Get the most recent description
            $latestDescription = $timerDescriptions->first();

            // Update the timer with this description
            DB::table('timers')
                ->where('id', $timerId)
                ->update(['description' => $latestDescription->description]);
        }
    }
};
