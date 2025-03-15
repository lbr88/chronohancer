<?php

use App\Http\Controllers\Auth\JiraAuthController;
use App\Http\Controllers\TempoAuthController;
use App\Livewire\Dashboard;
use App\Livewire\Projects;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\JiraIntegration;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TempoIntegration;
use App\Livewire\Tags;
use App\Livewire\TimeLogs;
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
    Route::get('/time-logs', TimeLogs::class)->name('time-logs');
    Route::get('/timers', Timers::class)->name('timers');
    Route::get('/workspaces', Workspaces::class)->name('workspaces');
    Route::get('/settings/profile', Profile::class)->name('settings.profile');
    Route::get('/settings/appearance', Appearance::class)->name('settings.appearance');
    Route::get('/settings/password', Password::class)->name('settings.password');
    Route::get('/settings/integrations/tempo', TempoIntegration::class)->name('settings.integrations.tempo');
    Route::get('/settings/integrations/jira', JiraIntegration::class)->name('settings.integrations.jira');
    Route::get('/auth/jira/callback', [JiraAuthController::class, 'callback'])->name('auth.jira.callback');

    // Tempo OAuth routes
    Route::get('/auth/tempo/redirect', [TempoAuthController::class, 'redirect'])->name('auth.tempo.redirect');
    Route::get('/auth/tempo/callback', [TempoAuthController::class, 'callback'])->name('auth.tempo.callback');
    Route::post('/auth/tempo/disconnect', [TempoAuthController::class, 'disconnect'])->name('auth.tempo.disconnect');
});

// Profile routes are handled by Livewire components

require __DIR__.'/auth.php';
