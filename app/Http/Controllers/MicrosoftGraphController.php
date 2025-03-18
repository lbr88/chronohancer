<?php

namespace App\Http\Controllers;

use App\Services\MicrosoftGraphService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MicrosoftGraphController extends Controller
{
  protected $microsoftGraphService;

  public function __construct(MicrosoftGraphService $microsoftGraphService)
  {
    $this->microsoftGraphService = $microsoftGraphService;
  }

  /**
   * Redirect the user to the Microsoft OAuth authorization page.
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function redirect()
  {
    $this->microsoftGraphService->setUser(Auth::user());
    return redirect()->away($this->microsoftGraphService->getAuthorizationUrl());
  }

  /**
   * Handle the callback from Microsoft OAuth.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function callback(Request $request)
  {
    try {
      $code = $request->query('code');
      if (!$code) {
        throw new Exception('Authorization code not provided');
      }

      $user = Auth::user();
      $this->microsoftGraphService->setUser($user);

      $tokenData = $this->microsoftGraphService->handleCallback($code);

      $user->update([
        'microsoft_enabled' => true,
        'microsoft_access_token' => $tokenData['access_token'],
        'microsoft_refresh_token' => $tokenData['refresh_token'],
        'microsoft_token_expires_at' => now()->addSeconds($tokenData['expires_in']),
      ]);

      return redirect()->route('settings.integrations.microsoft-calendar')
        ->with('status', 'Microsoft Calendar integration connected successfully!');
    } catch (Exception $e) {
      return redirect()->route('settings.profile')
        ->withErrors(['error' => 'Failed to connect Microsoft Calendar: ' . $e->getMessage()]);
    }
  }

  /**
   * Disconnect Microsoft Graph integration.
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function disconnect()
  {
    $user = Auth::user();
    $user->disconnectMicrosoft();

    return redirect()->route('settings.integrations.microsoft-calendar')
      ->with('status', 'Microsoft Calendar integration disconnected successfully.');
  }

  /**
   * Get the user's calendars.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function getCalendars()
  {
    $user = Auth::user();

    if (!$user->hasMicrosoftEnabled()) {
      return response()->json(['error' => 'Microsoft Calendar integration not enabled'], 400);
    }

    $calendars = $user->microsoft()->getCalendars();

    if (!$calendars) {
      return response()->json(['error' => 'Failed to fetch calendars'], 500);
    }

    return response()->json($calendars);
  }
  
  /**
   * Set the user's default calendar.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function setDefaultCalendar(Request $request)
  {
    $calendarId = $request->input('calendar_id');
    
    if (!$calendarId) {
      return response()->json(['error' => 'Calendar ID is required'], 400);
    }
    
    $user = Auth::user();
    
    if (!$user->hasMicrosoftEnabled()) {
      return response()->json(['error' => 'Microsoft Calendar integration not enabled'], 400);
    }
    
    $user->update([
      'microsoft_calendar_id' => $calendarId
    ]);
    
    return response()->json(['message' => 'Default calendar updated successfully']);
  }

  /**
   * Get events from the user's calendar.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getEvents(Request $request)
  {
    $user = Auth::user();

    if (!$user->hasMicrosoftEnabled()) {
      return response()->json(['error' => 'Microsoft Calendar integration not enabled'], 400);
    }

    $calendarId = $request->input('calendar_id', 'primary');
    $startDateTime = $request->input('start_date') ? now()->parse($request->input('start_date')) : now();
    $endDateTime = $request->input('end_date') ? now()->parse($request->input('end_date')) : now()->addDays(7);

    $events = $user->microsoft()->getEvents($calendarId, $startDateTime, $endDateTime);

    if (!$events) {
      return response()->json(['error' => 'Failed to fetch events'], 500);
    }

    return response()->json($events);
  }
}
