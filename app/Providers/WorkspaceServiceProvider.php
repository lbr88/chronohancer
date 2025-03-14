<?php

namespace App\Providers;

use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class WorkspaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('current.workspace', function ($app) {
            if (!Auth::check()) {
                return null;
            }
            
            $workspaceId = Session::get('current_workspace_id');
            
            if ($workspaceId) {
                $workspace = Workspace::find($workspaceId);
                if ($workspace && $workspace->user_id === Auth::id()) {
                    return $workspace;
                }
            }
            
            // If no valid workspace is found in the session, use the default one
            $defaultWorkspace = Workspace::where('user_id', Auth::id())
                ->where('is_default', true)
                ->first();
                
            if ($defaultWorkspace) {
                Session::put('current_workspace_id', $defaultWorkspace->id);
                return $defaultWorkspace;
            }
            
            // If no default workspace exists, create one
            $workspace = Workspace::findOrCreateDefault(Auth::id());
            Session::put('current_workspace_id', $workspace->id);
            
            return $workspace;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share the current workspace with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $view->with('currentWorkspace', app('current.workspace'));
            }
        });
        
        // Add a global scope to all workspace-related models
        $this->addWorkspaceScopes();
    }
    
    /**
     * Add global scopes to workspace-related models
     */
    protected function addWorkspaceScopes(): void
    {
        // Add global scopes to filter by the current workspace
        \App\Models\Project::addGlobalScope('workspace', function ($builder) {
            if (Auth::check() && app('current.workspace')) {
                $builder->where('workspace_id', app('current.workspace')->id);
            }
        });
        
        \App\Models\Tag::addGlobalScope('workspace', function ($builder) {
            if (Auth::check() && app('current.workspace')) {
                $builder->where('workspace_id', app('current.workspace')->id);
            }
        });
        
        \App\Models\Timer::addGlobalScope('workspace', function ($builder) {
            if (Auth::check() && app('current.workspace')) {
                $builder->where('workspace_id', app('current.workspace')->id);
            }
        });
        
        \App\Models\TimeLog::addGlobalScope('workspace', function ($builder) {
            if (Auth::check() && app('current.workspace')) {
                $builder->where('workspace_id', app('current.workspace')->id);
            }
        });
    }
}
