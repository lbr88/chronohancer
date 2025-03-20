<?php

use App\Http\Controllers\Auth\JiraAuthController;
use App\Http\Controllers\JiraController;
use App\Http\Controllers\MicrosoftCalendarController;
use App\Http\Controllers\MicrosoftGraphController;
use App\Http\Controllers\TempoAuthController;
use App\Livewire\Dashboard;
use App\Livewire\Projects;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\JiraIntegration;
use App\Livewire\Settings\MicrosoftCalendarIntegration;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TempoIntegration;
use App\Livewire\Tags;
use App\Livewire\TimeLogsBase;
use App\Livewire\Timers;
use App\Livewire\Workspaces;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/home', function () {
    return view('home');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/projects', Projects::class)->name('projects');
    Route::get('/tags', Tags::class)->name('tags');
    Route::get('/time-logs', TimeLogsBase::class)->name('time-logs');
    Route::get('/timers', Timers::class)->name('timers');
    Route::get('/workspaces', Workspaces::class)->name('workspaces');
    Route::get('/settings/profile', Profile::class)->name('settings.profile');
    Route::get('/settings/appearance', Appearance::class)->name('settings.appearance');

    if (env('ENABLE_EMAIL_SIGNUP', true)) {
        Route::get('/settings/password', Password::class)->name('settings.password');
    }
    Route::get('/settings/integrations/tempo', TempoIntegration::class)->name('settings.integrations.tempo');
    Route::get('/settings/integrations/jira', JiraIntegration::class)->name('settings.integrations.jira');
    Route::get('/settings/integrations/microsoft-calendar', MicrosoftCalendarIntegration::class)->name('settings.integrations.microsoft-calendar');
    Route::get('/auth/jira/callback', [JiraAuthController::class, 'callback'])->name('auth.jira.callback');
    Route::get('/api/jira/issue/{key}', [JiraController::class, 'getIssue'])->name('jira.issue');

    // Tempo OAuth routes
    Route::get('/auth/tempo/redirect', [TempoAuthController::class, 'redirect'])->name('auth.tempo.redirect');
    Route::get('/auth/tempo/callback', [TempoAuthController::class, 'callback'])->name('auth.tempo.callback');
    Route::post('/auth/tempo/disconnect', [TempoAuthController::class, 'disconnect'])->name('auth.tempo.disconnect');

    // Microsoft Graph routes
    Route::get('/auth/microsoft-graph/redirect', [MicrosoftGraphController::class, 'redirect'])->name('auth.microsoft-graph.redirect');
    Route::get('/auth/microsoft-graph/callback', [MicrosoftGraphController::class, 'callback'])->name('auth.microsoft-graph.callback');
    Route::get('/auth/microsoft-graph/disconnect', [MicrosoftGraphController::class, 'disconnect'])->name('auth.microsoft-graph.disconnect');
    Route::get('/api/microsoft-graph/calendars', [MicrosoftGraphController::class, 'getCalendars'])->name('microsoft-graph.calendars');
    Route::get('/api/microsoft-graph/events', [MicrosoftGraphController::class, 'getEvents'])->name('microsoft-graph.events');
    Route::post('/api/microsoft-graph/set-default-calendar', [MicrosoftGraphController::class, 'setDefaultCalendar'])->name('microsoft-graph.set-default-calendar');
    Route::get('/api/microsoft-calendar/weekly-events', [MicrosoftCalendarController::class, 'getWeeklyEvents'])->name('microsoft-calendar.weekly-events');
});

// Profile routes are handled by Livewire components

require __DIR__.'/auth.php';
