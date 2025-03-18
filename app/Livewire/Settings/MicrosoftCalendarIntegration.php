<?php

namespace App\Livewire\Settings;

use App\Services\MicrosoftGraphService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MicrosoftCalendarIntegration extends Component
{
  public $isConnected = false;

  public $microsoftEnabled = false;
  
  public $calendars = [];
  
  public $selectedCalendarId = null;
  
  public $loadingCalendars = false;

  public function mount()
  {
    $user = Auth::user();
    $this->isConnected = $user->hasMicrosoftEnabled();
    $this->microsoftEnabled = $user->microsoft_enabled;
    $this->selectedCalendarId = $user->microsoft_calendar_id;
    
    if ($this->isConnected) {
      $this->fetchCalendars();
    }
  }

  public function updatedMicrosoftEnabled($value)
  {
    Auth::user()->update(['microsoft_enabled' => $value]);
    $this->dispatch('microsoft-status-updated');
  }

  public function connect()
  {
    return redirect()->route('auth.microsoft-graph.redirect');
  }

  public function disconnect()
  {
    return redirect()->route('auth.microsoft-graph.disconnect');
  }
  
  public function fetchCalendars()
  {
    $this->loadingCalendars = true;
    
    try {
      $response = Auth::user()->microsoft()->getCalendars();
      
      if ($response && isset($response['value'])) {
        $this->calendars = $response['value'];
      } else {
        $this->calendars = [];
      }
    } catch (\Exception $e) {
      $this->addError('calendars', 'Failed to fetch calendars: ' . $e->getMessage());
      $this->calendars = [];
    }
    
    $this->loadingCalendars = false;
  }
  
  public function selectCalendar($calendarId)
  {
    $this->selectedCalendarId = $calendarId;
    Auth::user()->update(['microsoft_calendar_id' => $calendarId]);
    
    session()->flash('status', 'Calendar selection saved successfully.');
  }

  public function render()
  {
    return view('livewire.settings.microsoft-calendar-integration');
  }
}
