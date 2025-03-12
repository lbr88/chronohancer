<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Appearance extends Component
{
    public string $time_format = 'human';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->time_format = Auth::user()->time_format ?? 'human';
    }

    /**
     * Update the time format preference.
     */
    public function updateTimeFormat(): void
    {
        $validated = $this->validate([
            'time_format' => [
                'required',
                'string',
                Rule::in(['human', 'hm', 'hms']),
            ],
        ]);

        $user = Auth::user();
        $user->time_format = $validated['time_format'];
        $user->save();

        $this->dispatch('appearance-updated');
    }
    
    /**
     * Automatically save time format when it changes.
     */
    public function updatedTimeFormat(): void
    {
        $this->updateTimeFormat();
    }
}
