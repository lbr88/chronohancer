<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tag;
use App\Models\TimeLog;
use App\Models\Timer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();
        
        $activeTimers = Timer::with(['project', 'tags'])
            ->where('is_running', true)
            ->get();
            
        $projects = Project::all();
        $tags = Tag::all();
        
        // Get all time logs for the week
        $weeklyLogs = TimeLog::with(['timer.project', 'timer.tags'])
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->get();
            
        // Group time logs by day and project
        $dailySummary = [];
        $projectSummary = [];
        
        foreach ($weeklyLogs as $log) {
            $day = Carbon::parse($log->start_time)->format('Y-m-d');
            $projectId = $log->timer->project_id;
            $projectName = $log->timer->project->name;
            $duration = $log->duration ?? 0;
            
            if (!isset($dailySummary[$day])) {
                $dailySummary[$day] = 0;
            }
            $dailySummary[$day] += $duration;
            
            if (!isset($projectSummary[$projectId])) {
                $projectSummary[$projectId] = [
                    'name' => $projectName,
                    'duration' => 0,
                    'timers' => []
                ];
            }
            $projectSummary[$projectId]['duration'] += $duration;
            
            $timerId = $log->timer_id;
            if (!isset($projectSummary[$projectId]['timers'][$timerId])) {
                $projectSummary[$projectId]['timers'][$timerId] = [
                    'name' => $log->timer->name,
                    'duration' => 0
                ];
            }
            $projectSummary[$projectId]['timers'][$timerId]['duration'] += $duration;
        }
        
        return view('dashboard', compact(
            'activeTimers', 
            'projects', 
            'tags', 
            'dailySummary', 
            'projectSummary',
            'startOfWeek',
            'endOfWeek'
        ));
    }
}
