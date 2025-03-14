<?php

use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Timer;
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
        // First, update any existing timers with null project_id to use the default project
        $timers = Timer::whereNull('project_id')->get();
        foreach ($timers as $timer) {
            $defaultProject = Project::findOrCreateDefault($timer->user_id, $timer->workspace_id);
            $timer->project_id = $defaultProject->id;
            $timer->save();
        }

        // Then update any existing time logs with null project_id to use the default project
        $timeLogs = TimeLog::whereNull('project_id')->get();
        foreach ($timeLogs as $timeLog) {
            $defaultProject = Project::findOrCreateDefault($timeLog->user_id, $timeLog->workspace_id);
            $timeLog->project_id = $defaultProject->id;
            $timeLog->save();
        }

        // Now make project_id non-nullable in timers table
        Schema::table('timers', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable(false)->change();
        });

        // Make project_id non-nullable in time_logs table
        Schema::table('time_logs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make project_id nullable again in timers table
        Schema::table('timers', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->change();
        });

        // Make project_id nullable again in time_logs table
        Schema::table('time_logs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->change();
        });
    }
};
