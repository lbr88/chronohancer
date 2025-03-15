<?php

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
        Schema::table('time_logs', function (Blueprint $table) {
            $table->string('tempo_worklog_id')->nullable()->after('workspace_id');
            $table->timestamp('synced_to_tempo_at')->nullable()->after('tempo_worklog_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropColumn('tempo_worklog_id');
            $table->dropColumn('synced_to_tempo_at');
        });
    }
};
