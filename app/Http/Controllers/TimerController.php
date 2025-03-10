<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tag;
use App\Models\TimeLog;
use App\Models\Timer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimerController extends Controller
{
    public function index()
    {
        $timers = Timer::with(['project', 'tags'])->get();
        $projects = Project::all();
        $tags = Tag::all();
        
        return view('timers.index', compact('timers', 'projects', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'project_name' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'new_tags' => 'nullable|string',
        ]);

        // Create new project if needed
        if (empty($validated['project_id']) && !empty($validated['project_name'])) {
            $project = Project::create(['name' => $validated['project_name']]);
            $validated['project_id'] = $project->id;
        }

        // Create the timer
        $timer = Timer::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'project_id' => $validated['project_id'],
            'is_running' => true,
        ]);

        // Handle tags
        if (!empty($validated['tags'])) {
            $timer->tags()->attach($validated['tags']);
        }

        // Process new tags
        if (!empty($validated['new_tags'])) {
            $tagNames = array_map('trim', explode(',', $validated['new_tags']));
            foreach ($tagNames as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    if (!$timer->tags->contains($tag->id)) {
                        $timer->tags()->attach($tag->id);
                    }
                }
            }
        }

        // Start the timer
        TimeLog::create([
            'timer_id' => $timer->id,
            'start_time' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json($timer->load('project', 'tags'), 201);
        }

        return redirect()->route('dashboard')->with('success', 'Timer started successfully.');
    }

    public function start(Timer $timer)
    {
        if (!$timer->is_running) {
            $timer->update(['is_running' => true]);
            
            TimeLog::create([
                'timer_id' => $timer->id,
                'start_time' => now(),
            ]);
        }
        
        return redirect()->back()->with('success', 'Timer started.');
    }

    public function stop(Timer $timer)
    {
        if ($timer->is_running) {
            $timer->update(['is_running' => false]);
            
            $timeLog = TimeLog::where('timer_id', $timer->id)
                ->whereNull('end_time')
                ->latest()
                ->first();
                
            if ($timeLog) {
                $startTime = Carbon::parse($timeLog->start_time);
                $endTime = now();
                
                $timeLog->update([
                    'end_time' => $endTime,
                    'duration' => $endTime->diffInSeconds($startTime),
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Timer stopped.');
    }

    public function update(Request $request, Timer $timer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $timer->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'project_id' => $validated['project_id'],
        ]);

        if (isset($validated['tags'])) {
            $timer->tags()->sync($validated['tags']);
        }

        return redirect()->route('timers.index')->with('success', 'Timer updated successfully.');
    }

    public function destroy(Timer $timer)
    {
        $timer->delete();
        
        return redirect()->route('timers.index')->with('success', 'Timer deleted successfully.');
    }

    public function autocomplete(Request $request)
    {
        $query = $request->get('query');
        
        $timers = Timer::where('name', 'like', "%{$query}%")
            ->with(['project', 'tags'])
            ->limit(10)
            ->get();
            
        return response()->json($timers);
    }
}
