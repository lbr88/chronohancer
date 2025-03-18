<?php

namespace App\Livewire;

use App\Models\TimeLog;
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

    protected $listeners = [
        'weekChanged' => 'updateDateRange',
        'load-events' => 'loadEvents',
        'timeLogSaved' => 'loadEvents',
    ];

    public function mount($days = 7, $calendarId = null, $startOfWeek = null, $endOfWeek = null)
    {
        // If startOfWeek and endOfWeek are provided, use them
        // Otherwise, use the current week (Monday to Sunday)
        if ($startOfWeek && $endOfWeek) {
            $this->startDate = Carbon::parse($startOfWeek)->startOfDay();
            $this->endDate = Carbon::parse($endOfWeek)->endOfDay();
        } else {
            // Use the current week instead of a fixed number of days
            $this->startDate = now()->startOfWeek()->startOfDay();
            $this->endDate = now()->endOfWeek()->endOfDay();
        }

        $user = Auth::user();
        // Use the user's selected calendar if available, otherwise use the provided calendar or default to 'primary'
        $this->calendarId = $user->microsoft_calendar_id ?? $calendarId ?? 'primary';

        // Automatically load events when component is mounted
        $this->loadEvents();

        // Add a small delay to ensure the component is fully rendered before loading events again
        // This helps with the issue where events don't load when switching views
        $this->dispatch(function () {
            $this->loadEvents();
        });
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
                // Get all time logs for the current user within the date range
                $timeLogs = TimeLog::where('user_id', Auth::id())
                    ->where('workspace_id', app('current.workspace')->id)
                    ->whereBetween('start_time', [
                        Carbon::parse($this->startDate)->format('Y-m-d H:i:s'),
                        Carbon::parse($this->endDate)->format('Y-m-d H:i:s'),
                    ])
                    ->get();

                // Extract all logged Microsoft event IDs
                $loggedEventIds = $timeLogs->pluck('microsoft_event_id')->filter()->toArray();

                // Also extract descriptions for backward compatibility
                $loggedDescriptions = $timeLogs->pluck('description')->filter()->map(function ($desc) {
                    return strtolower(trim($desc));
                })->toArray();

                $this->events = collect($response['value'])
                    ->map(function ($event) {
                        $start = $this->formatDateTime($event['start']['dateTime'] ?? null);
                        $end = $this->formatDateTime($event['end']['dateTime'] ?? null);
                        $durationMinutes = 0;

                        if ($start && $end) {
                            $durationMinutes = $start->diffInMinutes($end);
                        }

                        return [
                            'id' => $event['id'],
                            'subject' => $event['subject'] ?? 'No Subject',
                            'start' => $start,
                            'end' => $end,
                            'location' => $event['location']['displayName'] ?? '',
                            'isAllDay' => $event['isAllDay'] ?? false,
                            'organizer' => $event['organizer']['emailAddress']['name'] ?? '',
                            'status' => $event['showAs'] ?? '',
                            'duration_minutes' => $durationMinutes,
                        ];
                    })
                    // Filter out events that have already been logged
                    ->filter(function ($event) use ($loggedEventIds, $loggedDescriptions) {
                        // First check if the event ID is in the logged event IDs
                        if (in_array($event['id'], $loggedEventIds)) {
                            return false;
                        }

                        // For backward compatibility, also check descriptions
                        return ! in_array(strtolower(trim($event['subject'])), $loggedDescriptions);
                    })
                    ->toArray();
            }
        } catch (\Exception $e) {
            $this->error = __('Failed to load calendar events: ').$e->getMessage();
        }

        $this->loading = false;
    }

    /**
     * Update the date range when the week changes in the TimeLogs component
     */
    public function updateDateRange($startOfWeek, $endOfWeek)
    {
        // Log the update for debugging
        \Illuminate\Support\Facades\Log::info('MicrosoftCalendarEvents updateDateRange', [
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'current_startDate' => $this->startDate ? $this->startDate->format('Y-m-d') : null,
            'current_endDate' => $this->endDate ? $this->endDate->format('Y-m-d') : null,
        ]);

        // Update the date range
        $this->startDate = Carbon::parse($startOfWeek)->startOfDay();
        $this->endDate = Carbon::parse($endOfWeek)->endOfDay();

        // Force a reload of events
        $this->loading = true;
        $this->events = [];
        $this->loadEvents();

        // Force a re-render to update the UI
        $this->dispatch('$refresh');
    }

    /**
     * Create a time log from a calendar event
     */
    public function createTimeLogFromEvent($eventId, $subject, $durationMinutes)
    {
        // Find the event
        $event = collect($this->events)->firstWhere('id', $eventId);

        if (! $event) {
            return;
        }

        $date = $event['start']->format('Y-m-d');

        $this->dispatch('createTimeLogFromEvent', [
            'date' => $date,
            'description' => $subject,
            'duration_minutes' => $durationMinutes,
            'event_id' => $eventId, // Pass the Microsoft event ID
        ]);
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
