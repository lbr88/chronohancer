<?php

use App\Models\TimeLog;
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
        // First make project_id nullable again to allow for the transition
        Schema::table('time_logs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->change();
        });

        // Update any time logs to ensure they use the project from their timer
        $timeLogs = TimeLog::with('timer')->get();
        foreach ($timeLogs as $timeLog) {
            if ($timeLog->timer && $timeLog->timer->project_id) {
                // Only update if the project_id is different from the timer's project_id
                if ($timeLog->project_id !== $timeLog->timer->project_id) {
                    $timeLog->project_id = $timeLog->timer->project_id;
                    $timeLog->save();
                }
            }
        }

        // Now remove the project_id column from time_logs table
        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add project_id column back
        Schema::table('time_logs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('timer_id')->constrained()->onDelete('cascade');
        });

        // Restore project_id values from the associated timer
        $timeLogs = TimeLog::with('timer')->get();
        foreach ($timeLogs as $timeLog) {
            if ($timeLog->timer) {
                $timeLog->project_id = $timeLog->timer->project_id;
                $timeLog->save();
            }
        }

        // Make project_id required again
        Schema::table('time_logs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};
