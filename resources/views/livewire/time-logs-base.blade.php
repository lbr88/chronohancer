<div class="timer-page">
  <h1 class="text-2xl font-semibold mb-4 dark:text-white">Time Logs</h1>

  @if (session()->has('message'))
  <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-4" role="alert">
    <p>{{ session('message') }}</p>
  </div>
  @endif

  <script>
    document.addEventListener('livewire:initialized', function() {
      Livewire.on('scroll-to-form', () => {
        const formElement = document.getElementById('time-log-form');
        if (formElement) {
          formElement.scrollIntoView({
            behavior: 'smooth'
          });
          // Highlight the form briefly
          formElement.classList.add('bg-indigo-50');
          setTimeout(() => {
            formElement.classList.remove('bg-indigo-50');
          }, 1500);
        }
      });
    });
  </script>

  <!-- View Switcher and Controls -->
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
    <div>
      <div class="inline-flex rounded-md shadow-sm" role="group">
        <button wire:click="switchView('list')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'list' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-l-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
          List
        </button>
        <button wire:click="switchView('weekly')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'weekly' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300' }} border border-gray-200 dark:border-gray-700 rounded-r-lg hover:bg-gray-100 dark:hover:bg-zinc-700">
          Calendar
        </button>
      </div>
    </div>

    <div class="flex flex-wrap items-center space-x-2">
      @if(config('tempo.enabled') && auth()->user()->hasTempoEnabled())
      <!-- Tempo Sync Component -->
      <livewire:tempo-sync />
      @endif

      <!-- Time Format Selector -->
      <livewire:time-logs-filters :startOfWeek="$startOfWeek" :endOfWeek="$endOfWeek" />
    </div>
  </div>

  <!-- Main Content -->
  <div class="grid grid-cols-1 gap-6">
    @if($view === 'weekly')
    <livewire:time-logs-weekly-view :startOfWeek="$startOfWeek" :endOfWeek="$endOfWeek" :currentWeek="$currentWeek" />
    @else
    <livewire:time-logs-list-view :startOfWeek="$startOfWeek" :endOfWeek="$endOfWeek" :currentWeek="$currentWeek" />
    @endif
  </div>

  <!-- Modals Component -->
  <livewire:time-logs-modals />
</div>