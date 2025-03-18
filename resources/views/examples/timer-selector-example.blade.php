<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
      Timer Selector Example
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
          <h3 class="text-lg font-medium mb-4">Unified Timer Selector</h3>
          <p class="mb-6">This example demonstrates how to use the UnifiedTimerSelector component to select or create timers across the application.</p>

          <div class="bg-gray-50 dark:bg-zinc-700 p-6 rounded-lg">
            <livewire:components.unified-timer-selector />
          </div>

          <div class="mt-8">
            <h4 class="text-md font-medium mb-2">How to Use</h4>
            <div class="bg-gray-50 dark:bg-zinc-700 p-4 rounded-lg">
              <pre class="text-sm text-gray-800 dark:text-gray-200 overflow-x-auto">
&lt;livewire:components.unified-timer-selector 
    :timer-id="$timerId" 
    :timer-description-id="$timerDescriptionId" 
    :project-id="$projectId" 
/&gt;
                            </pre>
            </div>

            <p class="mt-4">Listen for the <code>unified-timer-selected</code> event to get all selected values:</p>
            <div class="bg-gray-50 dark:bg-zinc-700 p-4 rounded-lg mt-2">
              <pre class="text-sm text-gray-800 dark:text-gray-200 overflow-x-auto">
protected $listeners = [
    'unified-timer-selected' => 'handleUnifiedTimerSelected',
];

public function handleUnifiedTimerSelected($data)
{
    // Access selected values
    $timerId = $data['timerId'];
    $timerName = $data['timerName'];
    $timerDescriptionId = $data['timerDescriptionId'];
    $description = $data['description'];
    $projectId = $data['projectId'];
    $projectName = $data['projectName'];
    
    // Do something with the data...
}
                            </pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>