<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MicrosoftCalendarWeeklyEvents extends Component
{
    public $startOfWeek;

    public $endOfWeek;

    public $events = [];

    public $loading = true;

    public $error = null;

    public $weekDays = [];

    public $initialized = false;

    protected $listeners = [
        'weekChanged' => 'updateWeekRange',
    ];

    // Track the last loaded week range to prevent duplicate loads - must be public to persist between requests
    public $lastLoadedRange = null;

    public function mount($startOfWeek = null, $endOfWeek = null)
    {
        $this->startOfWeek = $startOfWeek ?? now()->startOfWeek()->format('Y-m-d');
        $this->endOfWeek = $endOfWeek ?? now()->endOfWeek()->format('Y-m-d');
        $this->initializeWeekDays();

        // Set the last loaded range to prevent duplicate loads
        $this->lastLoadedRange = $this->startOfWeek.'_'.$this->endOfWeek;

        // Initialize component state
        $this->initialized = true;

        // Set loading state to true to show loading indicator
        $this->loading = true;

        // Call refresh directly - this will reset state and load events
        $this->refresh();
    }

    public function updateWeekRange($startOfWeek, $endOfWeek)
    {
        // Create a unique key for this week range
        $newRangeKey = $startOfWeek.'_'.$endOfWeek;

        Log::info('MicrosoftCalendarWeeklyEvents updateWeekRange', [
            'old_start' => $this->startOfWeek,
            'old_end' => $this->endOfWeek,
            'new_start' => $startOfWeek,
            'new_end' => $endOfWeek,
            'last_loaded_range' => $this->lastLoadedRange,
            'new_range_key' => $newRangeKey,
            'is_same_range' => $this->lastLoadedRange === $newRangeKey,
        ]);

        // Skip if this is the same range we've already loaded
        if ($this->lastLoadedRange === $newRangeKey) {
            Log::info('MicrosoftCalendarWeeklyEvents skipping duplicate load', [
                'range_key' => $newRangeKey,
            ]);

            return;
        }

        // Set the last loaded range before loading to prevent duplicate loads
        $this->lastLoadedRange = $newRangeKey;

        // Only update if the week range has actually changed
        if ($this->startOfWeek !== $startOfWeek || $this->endOfWeek !== $endOfWeek) {
            $this->startOfWeek = $startOfWeek;
            $this->endOfWeek = $endOfWeek;
            $this->initializeWeekDays();

            // Reset retry attempts when week range changes
            $this->retryAttempts = 0;

            // Load events with a slight delay to prevent multiple loads
            $this->loading = true;
            $this->dispatch(function () {
                $this->loadEvents(true); // Force refresh when week changes
            });
        }
    }

    private function initializeWeekDays()
    {
        $startDate = Carbon::parse($this->startOfWeek);
        $this->weekDays = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $this->weekDays[$date->format('Y-m-d')] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'dayName' => $date->format('l'),
                'events' => [],
            ];
        }
    }

    // Track if we've already attempted to load events for this week range
    protected $loadedRanges = [];

    // Track retry attempts to prevent infinite loops
    protected $retryAttempts = 0;

    protected $maxRetryAttempts = 3;

    /**
     * Force a refresh of the calendar events
     */
    public function refresh()
    {
        Log::info('MicrosoftCalendarWeeklyEvents refresh called');

        // Reset component state
        $this->loading = false;
        $this->error = null;
        $this->retryAttempts = 0;

        // Clear the loaded ranges to force a fresh load
        $this->loadedRanges = [];

        // Force refresh events
        $this->loadEvents(true);
    }

    public function loadEvents($forceRefresh = false)
    {
        $this->initialized = true;

        // Create a unique key for this week range
        $rangeKey = $this->startOfWeek.'_'.$this->endOfWeek;

        // Double-check that this matches our lastLoadedRange to prevent duplicate loads
        if ($this->lastLoadedRange !== $rangeKey) {
            $this->lastLoadedRange = $rangeKey;
            Log::info('MicrosoftCalendarWeeklyEvents updating lastLoadedRange in loadEvents', [
                'range_key' => $rangeKey,
            ]);
        }

        // Skip if we're already loading
        if ($this->loading) {
            Log::info('MicrosoftCalendarWeeklyEvents loadEvents - already loading, skipping');

            return;
        }

        // Check retry attempts to prevent infinite loops
        if ($this->retryAttempts >= $this->maxRetryAttempts) {
            Log::warning('MicrosoftCalendarWeeklyEvents loadEvents - max retry attempts reached', [
                'retry_attempts' => $this->retryAttempts,
                'max_retry_attempts' => $this->maxRetryAttempts,
            ]);
            $this->error = __('Failed to load calendar events after multiple attempts. Please try refreshing the page.');
            $this->loading = false;

            return;
        }

        // Set loading state before making API call
        $this->loading = true;
        $this->error = null;
        $this->retryAttempts++;

        // Mark this range as loaded
        $this->loadedRanges[$rangeKey] = now();

        Log::info('MicrosoftCalendarWeeklyEvents loadEvents', [
            'start_of_week' => $this->startOfWeek,
            'end_of_week' => $this->endOfWeek,
            'range_key' => $rangeKey,
            'retry_attempt' => $this->retryAttempts,
            'last_loaded_range' => $this->lastLoadedRange,
            'force_refresh' => $forceRefresh,
        ]);

        $user = Auth::user();

        if (! $user || ! $user->hasMicrosoftEnabled()) {
            $this->error = __('Microsoft Calendar integration is not enabled.');
            $this->loading = false;

            return;
        }

        try {
            $calendarId = $user->microsoft_calendar_id ?? 'primary';
            $startDateTime = Carbon::parse($this->startOfWeek);
            $endDateTime = Carbon::parse($this->endOfWeek)->endOfDay();

            Log::info('MicrosoftCalendarWeeklyEvents making API call', [
                'calendar_id' => $calendarId,
                'start_date' => $startDateTime->format('Y-m-d'),
                'end_date' => $endDateTime->format('Y-m-d'),
            ]);

            $response = $user->microsoft()->getEvents(
                $calendarId,
                $startDateTime,
                $endDateTime
            );

            Log::info('MicrosoftCalendarWeeklyEvents API response received', [
                'success' => $response !== null,
                'has_events' => $response && isset($response['value']),
                'event_count' => $response && isset($response['value']) ? count($response['value']) : 0,
            ]);

            if ($response && isset($response['value'])) {
                $this->processEvents($response['value']);
                // Reset retry attempts on success
                $this->retryAttempts = 0;
            } else {
                $this->error = __('No calendar data received from Microsoft. Please try again later.');
                Log::warning('MicrosoftCalendarWeeklyEvents - No data received from Microsoft API', [
                    'user_id' => $user->id,
                    'calendar_id' => $calendarId,
                ]);
            }
        } catch (\Exception $e) {
            $this->error = __('Failed to load calendar events: ').$e->getMessage();
            Log::error('MicrosoftCalendarWeeklyEvents loadEvents error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Always ensure loading is set to false, even if there's an exception
        $this->loading = false;

        // Force a re-render to ensure the UI updates
        $this->dispatch('$refresh');
    }

    private function processEvents($events)
    {
        Log::info('MicrosoftCalendarWeeklyEvents processEvents', [
            'event_count' => count($events),
        ]);

        // Reset events in weekDays
        foreach ($this->weekDays as $date => $day) {
            $this->weekDays[$date]['events'] = [];
        }

        foreach ($events as $event) {
            try {
                $startDateTime = isset($event['start']['dateTime'])
                  ? Carbon::parse($event['start']['dateTime'])
                  : null;

                $endDateTime = isset($event['end']['dateTime'])
                  ? Carbon::parse($event['end']['dateTime'])
                  : null;

                if (! $startDateTime || ! $endDateTime) {
                    Log::warning('MicrosoftCalendarWeeklyEvents - Event missing start or end time', [
                        'event_id' => $event['id'] ?? 'unknown',
                    ]);

                    continue;
                }

                $eventDate = $startDateTime->format('Y-m-d');

                // Skip if the event date is not in our week range
                if (! isset($this->weekDays[$eventDate])) {
                    Log::info('MicrosoftCalendarWeeklyEvents - Event date not in week range', [
                        'event_date' => $eventDate,
                        'event_id' => $event['id'] ?? 'unknown',
                    ]);

                    continue;
                }

                $durationMinutes = $startDateTime->diffInMinutes($endDateTime);

                $processedEvent = [
                    'id' => $event['id'],
                    'subject' => $event['subject'] ?? 'No Subject',
                    'start' => $startDateTime->format('H:i'),
                    'end' => $endDateTime->format('H:i'),
                    'location' => $event['location']['displayName'] ?? '',
                    'isAllDay' => $event['isAllDay'] ?? false,
                    'organizer' => $event['organizer']['emailAddress']['name'] ?? '',
                    'duration_minutes' => $durationMinutes,
                    'bodyPreview' => $event['bodyPreview'] ?? '',
                ];

                $this->weekDays[$eventDate]['events'][] = $processedEvent;

                Log::info('MicrosoftCalendarWeeklyEvents - Added event', [
                    'event_id' => $event['id'],
                    'subject' => $event['subject'] ?? 'No Subject',
                    'date' => $eventDate,
                ]);
            } catch (\Exception $e) {
                Log::error('MicrosoftCalendarWeeklyEvents - Error processing event', [
                    'event_id' => $event['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Sort events by start time
        foreach ($this->weekDays as $date => $day) {
            usort($this->weekDays[$date]['events'], function ($a, $b) {
                return strcmp($a['start'], $b['start']);
            });
        }

        // Log the final count of events per day
        $eventCounts = [];
        foreach ($this->weekDays as $date => $day) {
            $eventCounts[$date] = count($day['events']);
        }

        Log::info('MicrosoftCalendarWeeklyEvents - Final event counts', [
            'event_counts' => $eventCounts,
        ]);
    }

    public function createTimeLogFromEvent($date, $subject, $durationMinutes)
    {
        $this->dispatch('createTimeLogFromEvent', [
            'date' => $date,
            'description' => $subject,
            'duration_minutes' => $durationMinutes,
        ]);
    }

    public function render()
    {
        return view('livewire.microsoft-calendar-weekly-events');
    }
}
