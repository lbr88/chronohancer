<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use App\Models\TimeLog;
use App\Models\Timer;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;

class Workspaces extends Component
{
    use WithPagination;

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $workspaceToEdit = null;

    public $workspaceToDelete = null;

    public $isDefaultWorkspace = false;

    public $form = [
        'name' => '',
        'description' => '',
        'color' => '#6366f1', // Default indigo color
        'is_default' => false,
        'daily_target_minutes' => 444, // Default: 7.4 hours = 444 minutes
        'weekly_target_minutes' => 2220, // Default: 37 hours = 2220 minutes
    ];

    // Human-readable time strings
    public $dailyTargetTime = '7h 24m';

    public $weeklyTargetTime = '37h';

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.description' => 'nullable|string',
        'form.color' => 'required|string|max:7',
        'form.is_default' => 'boolean',
        'form.daily_target_minutes' => 'required|integer|min:0',
        'form.weekly_target_minutes' => 'required|integer|min:0',
    ];

    // Listen for changes to the human-readable time inputs
    protected $listeners = [
        'workspace-created' => '$refresh',
        'workspace-updated' => '$refresh',
        'workspace-deleted' => '$refresh',
    ];

    public function mount()
    {
        // Ensure the user has a default workspace
        Workspace::findOrCreateDefault(Auth::id());

        // Initialize human-readable time strings
        $this->updateHumanReadableTimes();
    }

    /**
     * Format minutes to a human-readable string (e.g., "7h 24m")
     */
    private function formatMinutesToHumanReadable(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours}h {$remainingMinutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$remainingMinutes}m";
        }
    }

    /**
     * Parse a human-readable string (e.g., "7h 24m") to minutes
     */
    private function parseHumanReadableToMinutes(string $timeString): int
    {
        $timeString = trim($timeString);
        $minutes = 0;

        // Match hours (e.g., "7h")
        if (preg_match('/(\d+)h/i', $timeString, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        // Match minutes (e.g., "24m")
        if (preg_match('/(\d+)m/i', $timeString, $matches)) {
            $minutes += (int) $matches[1];
        }

        return $minutes;
    }

    /**
     * Update human-readable time strings based on minute values
     */
    private function updateHumanReadableTimes()
    {
        $this->dailyTargetTime = $this->formatMinutesToHumanReadable($this->form['daily_target_minutes']);
        $this->weeklyTargetTime = $this->formatMinutesToHumanReadable($this->form['weekly_target_minutes']);
    }

    /**
     * Update daily target minutes when the human-readable daily target time changes
     */
    public function updatedDailyTargetTime()
    {
        $minutes = $this->parseHumanReadableToMinutes($this->dailyTargetTime);

        // Allow 0 as a valid value
        $this->form['daily_target_minutes'] = $minutes;

        // If daily target is 0, set weekly target to 0 as well
        if ($minutes === 0) {
            $this->form['weekly_target_minutes'] = 0;
            $this->weeklyTargetTime = '0h';
        } else {
            $this->form['weekly_target_minutes'] = $minutes * 5; // Assuming 5-day work week
            $this->weeklyTargetTime = $this->formatMinutesToHumanReadable($this->form['weekly_target_minutes']);
        }
    }

    /**
     * Update weekly target minutes when the human-readable weekly target time changes
     */
    public function updatedWeeklyTargetTime()
    {
        $minutes = $this->parseHumanReadableToMinutes($this->weeklyTargetTime);

        // Allow 0 as a valid value
        $this->form['weekly_target_minutes'] = $minutes;

        // If weekly target is 0, set daily target to 0 as well
        if ($minutes === 0) {
            $this->form['daily_target_minutes'] = 0;
            $this->dailyTargetTime = '0h';
        } else {
            $this->form['daily_target_minutes'] = (int) round($minutes / 5); // Assuming 5-day work week
            $this->dailyTargetTime = $this->formatMinutesToHumanReadable($this->form['daily_target_minutes']);
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(Workspace $workspace)
    {
        $this->resetForm();
        $this->workspaceToEdit = $workspace;
        $this->form = [
            'name' => $workspace->name,
            'description' => $workspace->description,
            'color' => $workspace->color,
            'is_default' => $workspace->is_default,
            'daily_target_minutes' => $workspace->daily_target_minutes,
            'weekly_target_minutes' => $workspace->weekly_target_minutes,
        ];

        // Update human-readable time strings
        $this->updateHumanReadableTimes();

        $this->showEditModal = true;
    }

    public function openDeleteModal(Workspace $workspace)
    {
        $this->workspaceToDelete = $workspace;
        $this->isDefaultWorkspace = $workspace->is_default;
        $this->showDeleteModal = true;
    }

    public function createWorkspace()
    {
        $this->validate();

        DB::transaction(function () {
            // If this is set as default, unset any existing default workspace
            if ($this->form['is_default']) {
                Workspace::where('user_id', Auth::id())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $workspace = Workspace::create([
                'name' => $this->form['name'],
                'description' => $this->form['description'],
                'color' => $this->form['color'],
                'is_default' => $this->form['is_default'],
                'daily_target_minutes' => $this->form['daily_target_minutes'],
                'weekly_target_minutes' => $this->form['weekly_target_minutes'],
                'user_id' => Auth::id(),
            ]);

            // Create a default project for this workspace
            Project::findOrCreateDefault(Auth::id(), $workspace->id);

            // If this is the first workspace or set as default, set it as the current workspace
            if ($this->form['is_default'] || Workspace::where('user_id', Auth::id())->count() === 1) {
                Session::put('current_workspace_id', $workspace->id);
            }
        });

        $this->dispatch('workspace-created');
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function updateWorkspace()
    {
        $this->validate();

        DB::transaction(function () {
            // If this is set as default, unset any existing default workspace
            if ($this->form['is_default'] && ! $this->workspaceToEdit->is_default) {
                Workspace::where('user_id', Auth::id())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $this->workspaceToEdit->update([
                'name' => $this->form['name'],
                'description' => $this->form['description'],
                'color' => $this->form['color'],
                'is_default' => $this->form['is_default'],
                'daily_target_minutes' => $this->form['daily_target_minutes'],
                'weekly_target_minutes' => $this->form['weekly_target_minutes'],
            ]);

            // If this is set as default, set it as the current workspace
            if ($this->form['is_default']) {
                Session::put('current_workspace_id', $this->workspaceToEdit->id);
            }
        });

        $this->dispatch('workspace-updated');
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function deleteWorkspace()
    {
        if ($this->isDefaultWorkspace) {
            return;
        }

        DB::transaction(function () {
            // Delete all related data
            Project::where('workspace_id', $this->workspaceToDelete->id)->delete();
            Tag::where('workspace_id', $this->workspaceToDelete->id)->delete();
            Timer::where('workspace_id', $this->workspaceToDelete->id)->delete();
            TimeLog::where('workspace_id', $this->workspaceToDelete->id)->delete();

            // Delete the workspace
            $this->workspaceToDelete->delete();

            // If the deleted workspace was the current one, switch to the default workspace
            if (Session::get('current_workspace_id') == $this->workspaceToDelete->id) {
                $defaultWorkspace = Workspace::where('user_id', Auth::id())
                    ->where('is_default', true)
                    ->first();

                if ($defaultWorkspace) {
                    Session::put('current_workspace_id', $defaultWorkspace->id);
                }
            }
        });

        $this->dispatch('workspace-deleted');
        $this->showDeleteModal = false;
    }

    public function setAsDefault(Workspace $workspace)
    {
        DB::transaction(function () use ($workspace) {
            // Unset any existing default workspace
            Workspace::where('user_id', Auth::id())
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set the selected workspace as default
            $workspace->update(['is_default' => true]);

            // Set it as the current workspace
            Session::put('current_workspace_id', $workspace->id);
        });

        $this->dispatch('workspace-updated');
    }

    private function resetForm()
    {
        $this->form = [
            'name' => '',
            'description' => '',
            'color' => '#6366f1', // Default indigo color
            'is_default' => false,
            'daily_target_minutes' => 444, // Default: 7.4 hours = 444 minutes
            'weekly_target_minutes' => 2220, // Default: 37 hours = 2220 minutes
        ];

        // Update human-readable time strings
        $this->updateHumanReadableTimes();

        $this->workspaceToEdit = null;
        $this->workspaceToDelete = null;
        $this->isDefaultWorkspace = false;
    }

    public function render()
    {
        $workspaces = Workspace::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.workspaces', [
            'workspaces' => $workspaces,
        ]);
    }
}
