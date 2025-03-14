<?php

namespace App\Livewire;

use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkspaceSelector extends Component
{
    public $workspaces = [];

    public $currentWorkspaceId;

    public function mount()
    {
        $this->loadWorkspaces();
        $this->currentWorkspaceId = Session::get('current_workspace_id');

        // If no workspace is selected, use the default one
        if (! $this->currentWorkspaceId) {
            $defaultWorkspace = Workspace::where('user_id', Auth::id())
                ->where('is_default', true)
                ->first();

            if ($defaultWorkspace) {
                $this->currentWorkspaceId = $defaultWorkspace->id;
                Session::put('current_workspace_id', $this->currentWorkspaceId);
            }
        }
    }

    #[On('workspace-created')]
    #[On('workspace-updated')]
    #[On('workspace-deleted')]
    public function loadWorkspaces()
    {
        $this->workspaces = Workspace::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function switchWorkspace($workspaceId)
    {
        $this->currentWorkspaceId = $workspaceId;
        Session::put('current_workspace_id', $workspaceId);

        $this->dispatch('workspace-switched', workspaceId: $workspaceId);

        // Refresh the page to ensure all components load with the new workspace
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.workspace-selector');
    }
}
