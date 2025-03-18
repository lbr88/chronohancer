<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MicrosoftCalendarEvents extends Component
{
    public $events = [];

    public $loading = true;

    public $error = null;

    public $startDate;

    public $endDate;

    public $calendarId = 'primary';

    public function mount($days = 7, $calendarId = null)
    {
        $this->startDate = now()->startOfDay();
        $this->endDate = now()->addDays($days)->endOfDay();

        $user = Auth::user();
        // Use the user's selected calendar if available, otherwise use the provided calendar or default to 'primary'
        $this->calendarId = $user->microsoft_calendar_id ?? $calendarId ?? 'primary';
    }

    public function loadEvents()
    {
        $this->loading = true;
        $this->error = null;
        $this->events = [];

        $user = Auth::user();

        if (! $user || ! $user->hasMicrosoftEnabled()) {
            $this->error = __('Microsoft Calendar integration is not enabled.');
            $this->loading = false;

            return;
        }

        try {
            $response = $user->microsoft()->getEvents(
                $this->calendarId,
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            );

            if ($response && isset($response['value'])) {
                $this->events = collect($response['value'])
                    ->map(function ($event) {
                        return [
                            'id' => $event['id'],
                            'subject' => $event['subject'] ?? 'No Subject',
                            'start' => $this->formatDateTime($event['start']['dateTime'] ?? null),
                            'end' => $this->formatDateTime($event['end']['dateTime'] ?? null),
                            'location' => $event['location']['displayName'] ?? '',
                            'isAllDay' => $event['isAllDay'] ?? false,
                            'organizer' => $event['organizer']['emailAddress']['name'] ?? '',
                            'status' => $event['showAs'] ?? '',
                        ];
                    })
                    ->toArray();
            }
        } catch (\Exception $e) {
            $this->error = __('Failed to load calendar events: ').$e->getMessage();
        }

        $this->loading = false;
    }

    private function formatDateTime($dateTime)
    {
        if (! $dateTime) {
            return null;
        }

        return Carbon::parse($dateTime)->setTimezone(config('app.timezone'));
    }

    public function render()
    {
        return view('livewire.microsoft-calendar-events');
    }
}
