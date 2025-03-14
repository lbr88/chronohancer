<?php

use App\Livewire\Dashboard;
use App\Livewire\Projects;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Tags;
use App\Livewire\TimeLogs;
use App\Livewire\Timers;
use App\Livewire\Workspaces;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
});

// Profile routes are handled by Livewire components

require __DIR__.'/auth.php';
