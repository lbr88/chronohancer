<?php

namespace App\Livewire\Components;

use Livewire\Component;

class TimeInput extends Component
{
    /**
     * The input value (can be in minutes, hh:mm, or Xh Ym format)
     */
    public $value;

    /**
     * The formatted value (in 1h 30m format)
     */
    public $formattedValue;

    /**
     * The value in minutes
     */
    public $minutes = 0;

    /**
     * Whether to show preset buttons
     */
    public $showPresets = true;

    /**
     * Whether to show increment/decrement buttons
     */
    public $showIncrementButtons = true;

    /**
     * The name of the input field
     */
    public $name;

    /**
     * The ID of the input field
     */
    public $inputId;

    /**
     * The label for the input field
     */
    public $label;

    /**
     * The placeholder for the input field
     */
    public $placeholder = 'Enter time (e.g. 1:30 or 1h30m)';

    /**
     * The CSS class for the input field
     */
    public $class = '';

    /**
     * The error message
     */
    public $error = '';

    /**
     * The help text
     */
    public $helpText = '';

    /**
     * Event listeners
     */
    protected $listeners = [
        'timeInputUpdated' => 'updateValue',
    ];

    /**
     * Mount the component
     */
    public function mount($value = null, $name = null, $inputId = null, $label = null, $showPresets = true, $showIncrementButtons = true, $placeholder = null, $class = null, $helpText = null)
    {
        $this->value = $value;
        $this->name = $name;
        $this->inputId = $inputId ?? $name;
        $this->label = $label;
        $this->showPresets = $showPresets;
        $this->showIncrementButtons = $showIncrementButtons;

        if ($placeholder) {
            $this->placeholder = $placeholder;
        }

        if ($class) {
            $this->class = $class;
        }

        if ($helpText) {
            $this->helpText = $helpText;
        }

        if ($value) {
            $this->updateValue($value);
        }
    }

    /**
     * Update the value
     */
    public function updateValue($value)
    {
        $this->value = $value;
        $this->minutes = $this->parseTimeInput($value);
        $this->formattedValue = $this->formatDuration($this->minutes);

        // Dispatch the updated value to the parent component
        $this->dispatch('time-input-changed', [
            'name' => $this->name,
            'value' => $this->value,
            'minutes' => $this->minutes,
            'formattedValue' => $this->formattedValue,
        ]);
    }

    /**
     * Handle input changes
     */
    public function updatedValue()
    {
        $this->updateValue($this->value);
    }

    /**
     * Add time
     */
    public function addTime($minutes)
    {
        $this->minutes += $minutes;
        $this->formattedValue = $this->formatDuration($this->minutes);
        $this->value = $this->formattedValue;

        // Dispatch the updated value to the parent component
        $this->dispatch('time-input-changed', [
            'name' => $this->name,
            'value' => $this->value,
            'minutes' => $this->minutes,
            'formattedValue' => $this->formattedValue,
        ]);
    }

    /**
     * Set time to a specific value
     */
    public function setTime($minutes)
    {
        $this->minutes = $minutes;
        $this->formattedValue = $this->formatDuration($this->minutes);
        $this->value = $this->formattedValue;

        // Dispatch the updated value to the parent component
        $this->dispatch('time-input-changed', [
            'name' => $this->name,
            'value' => $this->value,
            'minutes' => $this->minutes,
            'formattedValue' => $this->formattedValue,
        ]);
    }

    /**
     * Parse time input in various formats
     * Supports:
     * - Plain minutes (e.g. "90")
     * - Hours:Minutes (e.g. "1:30")
     * - XhYm format (e.g. "1h30m")
     */
    private function parseTimeInput($input)
    {
        // If empty, return 0
        if (empty($input)) {
            return 0;
        }

        // If it's already a number, return it
        if (is_numeric($input)) {
            return (int) $input;
        }

        // Try to parse as HH:MM format
        if (preg_match('/^(\d+):(\d+)$/', $input, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];

            return ($hours * 60) + $minutes;
        }

        // Try to parse as XhYm format
        $minutes = 0;

        // Match hours
        if (preg_match('/(\d+)h/', $input, $matches)) {
            $minutes += (int) $matches[1] * 60;
        }

        // Match minutes
        if (preg_match('/(\d+)m/', $input, $matches)) {
            $minutes += (int) $matches[1];
        }

        return $minutes ?: (int) $input; // Fallback to treating it as minutes
    }

    /**
     * Format duration in minutes to a human-readable format (e.g. "1h 30m")
     */
    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.components.time-input');
    }
}
