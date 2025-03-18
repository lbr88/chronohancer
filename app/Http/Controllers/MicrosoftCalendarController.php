<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MicrosoftCalendarController extends Controller
{
    /**
     * Get calendar events for the specified week.
     */
    public function getWeeklyEvents(Request $request)
    {
        $startOfWeek = $request->input('start_of_week', now()->startOfWeek()->format('Y-m-d'));
        $endOfWeek = $request->input('end_of_week', now()->endOfWeek()->format('Y-m-d'));

        Log::info('Microsoft Calendar weekly events request', [
            'start_of_week' => $startOfWeek,
            'end_of_week' => $endOfWeek,
            'request_url' => $request->fullUrl(),
        ]);

        $user = Auth::user();

        if (! $user) {
            Log::error('Microsoft Calendar weekly events - no authenticated user');

            return response()->json([
                'error' => 'No authenticated user.',
                'events' => [],
                'weekDays' => [],
            ]);
        }

        Log::info('Microsoft Calendar weekly events - user check', [
            'user_id' => $user->id,
            'microsoft_enabled' => $user->microsoft_enabled,
            'has_access_token' => ! empty($user->microsoft_access_token),
            'has_refresh_token' => ! empty($user->microsoft_refresh_token),
            'has_microsoft_enabled' => $user->hasMicrosoftEnabled(),
        ]);

        if (! $user->hasMicrosoftEnabled()) {
            Log::warning('Microsoft Calendar weekly events - integration not enabled', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'error' => 'Microsoft Calendar integration is not enabled.',
                'events' => [],
                'weekDays' => [],
            ]);
        }

        try {
            $calendarId = $user->microsoft_calendar_id ?? 'primary';
            $startDateTime = Carbon::parse($startOfWeek);
            $endDateTime = Carbon::parse($endOfWeek)->endOfDay();

            // Log the request parameters
            Log::info('Fetching Microsoft calendar events', [
                'user_id' => $user->id,
                'calendar_id' => $calendarId,
                'start_date' => $startDateTime->toIso8601String(),
                'end_date' => $endDateTime->toIso8601String(),
                'microsoft_enabled' => $user->microsoft_enabled,
                'has_access_token' => ! empty($user->microsoft_access_token),
                'has_refresh_token' => ! empty($user->microsoft_refresh_token),
            ]);

            $response = $user->microsoft()->getEvents(
                $calendarId,
                $startDateTime,
                $endDateTime
            );

            // Log the response
            Log::info('Microsoft calendar events response', [
                'user_id' => $user->id,
                'success' => ! empty($response),
                'has_value' => isset($response['value']),
                'event_count' => isset($response['value']) ? count($response['value']) : 0,
                'response_data' => $response,
            ]);

            $weekDays = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $startDateTime->copy()->addDays($i);
                $weekDays[$date->format('Y-m-d')] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->format('D'),
                    'dayName' => $date->format('l'),
                    'events' => [],
                ];
            }

            if ($response && isset($response['value'])) {
                foreach ($response['value'] as $event) {
                    $eventStartDateTime = isset($event['start']['dateTime'])
                      ? Carbon::parse($event['start']['dateTime'])
                      : null;

                    $eventEndDateTime = isset($event['end']['dateTime'])
                      ? Carbon::parse($event['end']['dateTime'])
                      : null;

                    if (! $eventStartDateTime || ! $eventEndDateTime) {
                        continue;
                    }

                    $eventDate = $eventStartDateTime->format('Y-m-d');

                    // Skip if the event date is not in our week range
                    if (! isset($weekDays[$eventDate])) {
                        continue;
                    }

                    $durationMinutes = $eventStartDateTime->diffInMinutes($eventEndDateTime);

                    $processedEvent = [
                        'id' => $event['id'],
                        'subject' => $event['subject'] ?? 'No Subject',
                        'start' => $eventStartDateTime->format('H:i'),
                        'end' => $eventEndDateTime->format('H:i'),
                        'location' => $event['location']['displayName'] ?? '',
                        'isAllDay' => $event['isAllDay'] ?? false,
                        'organizer' => $event['organizer']['emailAddress']['name'] ?? '',
                        'duration_minutes' => $durationMinutes,
                        'bodyPreview' => $event['bodyPreview'] ?? '',
                    ];

                    $weekDays[$eventDate]['events'][] = $processedEvent;
                }

                // Sort events by start time
                foreach ($weekDays as $date => $day) {
                    usort($weekDays[$date]['events'], function ($a, $b) {
                        return strcmp($a['start'], $b['start']);
                    });
                }
            }

            return response()->json([
                'error' => null,
                'weekDays' => $weekDays,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load calendar events: '.$e->getMessage(),
                'events' => [],
                'weekDays' => [],
            ]);
        }
    }
}
