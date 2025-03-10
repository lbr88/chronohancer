<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Timer;
use App\Models\Project;
use App\Models\TimeLog;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Timers extends Component
{
    public $project_name = '';
    public $name;
    public $description;
    public $tag_input = '';
    public $search = '';
    public Collection $existingTimers;
    public $suggestions = [];
    
    protected $listeners = ['timerTick' => 'updateTimerDisplay'];
    
    protected $rules = [
        'project_name' => 'nullable|string|max:255',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'tag_input' => 'nullable|string',
    ];

    public function mount()
    {
        // Initialize collections
        $this->existingTimers = collect();
        $this->suggestions = ['projects' => []];
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->existingTimers = Timer::with(['project', 'tags'])
                ->where('user_id', auth()->id())
                ->where('name', 'like', '%' . $this->search . '%')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            $this->existingTimers = collect();
        }
    }

    public function updatedProjectName()
    {
        if (strlen($this->project_name) >= 2) {
            $this->suggestions['projects'] = Project::with('tags')
                ->where('user_id', auth()->id())
                ->where('name', 'like', '%' . $this->project_name . '%')
                ->limit(5)
                ->get();
        } else {
            $this->suggestions['projects'] = [];
        }
    }

    public function selectProject($projectId)
    {
        $project = Project::find($projectId);
        if ($project) {
            $this->project_name = $project->name;
            // If the project has tags, add them to the tag input
            if ($project->tags->isNotEmpty()) {
                $projectTags = $project->tags->pluck('name')->implode(', ');
                $this->tag_input = $this->tag_input
                    ? $this->tag_input . ', ' . $projectTags
                    : $projectTags;
            }
        }
        $this->suggestions['projects'] = [];
    }
    
    public function useExistingTimer($timerId)
    {
        $timer = Timer::with(['tags', 'project'])->findOrFail($timerId);
        $this->name = $timer->name;
        $this->description = $timer->description;
        $this->project_name = $timer->project?->name ?? '';
        $this->tag_input = $timer->tags->pluck('name')->implode(', ');
        $this->search = '';
        $this->existingTimers = collect();
    }

    public function startTimer()
    {
        $this->validate();
        
        // Find or create project if name is provided
        $project_id = null;
        if ($this->project_name) {
            $project = Project::firstOrCreate(
                ['name' => $this->project_name, 'user_id' => auth()->id()],
                ['description' => 'Project created from timer']
            );
            $project_id = $project->id;
        }
        
        // Create new timer
        $timer = Timer::create([
            'user_id' => auth()->id(),
            'project_id' => $project_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_running' => true,
        ]);
        
        // Process tags
        if ($this->tag_input) {
            $tagNames = collect(explode(',', $this->tag_input))
                ->map(fn($name) => trim($name))
                ->filter();
                
            $tags = $tagNames->map(function($name) {
                return Tag::findOrCreateForUser($name, auth()->id());
            });
            
            $timer->tags()->attach($tags->pluck('id'));
            
            // If we have a project, also attach the tags to it
            if ($project_id) {
                $project->tags()->syncWithoutDetaching($tags->pluck('id'));
            }
        }
        
        // Create time log
        TimeLog::create([
            'timer_id' => $timer->id,
            'user_id' => auth()->id(),
            'project_id' => $project_id,
            'start_time' => now(),
            'description' => $this->description ?: null,
        ]);
        
        session()->flash('message', 'Timer started successfully.');
        $this->dispatch('timerStarted');
        
        // Reset form, making sure to initialize existingTimers as a collection
        $this->reset(['name', 'description', 'project_name', 'tag_input', 'search']);
        $this->existingTimers = collect();
        $this->suggestions = ['projects' => []];
    }
    
    public function stopTimer($timerId)
    {
        $timer = Timer::with('timeLogs')->findOrFail($timerId);
        $timer->is_running = false;
        $timer->save();
        
        // Get the latest time log for this timer
        $latestLog = $timer->timeLogs()->latest()->first();
        
        if ($latestLog && !$latestLog->end_time) {
            // Update the existing log with end time
            $latestLog->end_time = now();
            $latestLog->duration_minutes = $latestLog->start_time->diffInMinutes($latestLog->end_time);
            $latestLog->save();
        }
        
        session()->flash('message', 'Timer stopped and time log created.');
        $this->dispatch('timerStopped');
    }
    
    public function getContrastColor($hexColor)
    {
        $hexColor = ltrim($hexColor, '#');
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
        return ($luminance > 0.5) ? '#000000' : '#FFFFFF';
    }
    
    public function render()
    {
        // Cache recent tags for 5 minutes to improve performance
        $recentTags = Cache::remember('user.' . auth()->id() . '.recent_tags', 300, function () {
            return Tag::where('user_id', auth()->id())
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();
        });
            
        return view('livewire.timers', [
            'recentTags' => $recentTags,
            'runningTimers' => Timer::with(['project', 'tags', 'latestTimeLog'])
                ->where('user_id', auth()->id())
                ->where('is_running', true)
                ->get(),
        ]);
    }
}
