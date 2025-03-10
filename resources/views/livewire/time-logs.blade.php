<div>
    <h1 class="text-2xl font-semibold mb-4">Time Logs</h1>
    
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <!-- View Switcher -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="inline-flex rounded-md shadow-sm" role="group">
                <button wire:click="switchView('list')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-l-lg hover:bg-gray-100">
                    List View
                </button>
                <button wire:click="switchView('weekly')" type="button" class="px-4 py-2 text-sm font-medium {{ $view === 'weekly' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700' }} border border-gray-200 rounded-r-lg hover:bg-gray-100">
                    Weekly Summary
                </button>
            </div>
        </div>
    </div>

    @if($view === 'weekly')
        <!-- Weekly Summary View -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <button wire:click="previousWeek" class="px-3 py-1 border rounded-md hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                <h2 class="text-xl font-medium">
                    {{ Carbon\Carbon::parse($startOfWeek)->format('M d') }} - {{ Carbon\Carbon::parse($endOfWeek)->format('M d, Y') }}
                </h2>
                <div class="flex space-x-2">
                    <button wire:click="currentWeek" class="px-3 py-1 border rounded-md hover:bg-gray-100 {{ $currentWeek->isCurrentWeek() ? 'bg-gray-200' : '' }}">
                        Today
                    </button>
                    <button wire:click="nextWeek" class="px-3 py-1 border rounded-md hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                Projects & Tasks
                            </th>
                            @foreach($this->weeklyData['weekDays'] as $day)
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider {{ Carbon\Carbon::parse($day['date'])->isToday() ? 'bg-indigo-50' : '' }}">
                                    <div>{{ $day['day'] }}</div>
                                    <div>{{ Carbon\Carbon::parse($day['date'])->format('M d') }}</div>
                                </th>
                            @endforeach
                            <th scope="col" class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(count($this->weeklyData['projects']) > 0)
                            @foreach($this->weeklyData['projects'] as $project)
                                <!-- Project Row -->
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ $project['name'] }}
                                    </td>
                                    @foreach($this->weeklyData['weekDays'] as $day)
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500"></td>
                                    @endforeach
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold">
                                        {{ $this->formatDuration($project['total']) }}
                                    </td>
                                </tr>
                                
                                <!-- Timer Rows -->
                                @foreach($project['timers'] as $timer)
                                    <tr>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $timer['name'] }}
                                                    </div>
                                                    @if(count($timer['tags']) > 0)
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach($timer['tags'] as $tag)
                                                                <span class="inline-block px-2 py-0.5 text-xs rounded-full" 
                                                                    style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                                    {{ $tag->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($this->weeklyData['weekDays'] as $day)
                                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm {{ $timer['daily'][$day['date']] > 0 ? 'bg-indigo-50' : '' }}">
                                                @if($timer['daily'][$day['date']] > 0)
                                                    {{ $this->formatDuration($timer['daily'][$day['date']]) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                                            {{ $this->formatDuration($timer['total']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            
                            <!-- Grand Total Row -->
                            <tr class="bg-indigo-50 font-bold">
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
                                    Total Hours
                                </td>
                                @foreach($this->weeklyData['weekDays'] as $day)
                                    @php
                                        $dayTotal = 0;
                                        foreach($this->weeklyData['projects'] as $project) {
                                            foreach($project['timers'] as $timer) {
                                                $dayTotal += $timer['daily'][$day['date']];
                                            }
                                        }
                                    @endphp
                                    <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
                                        {{ $this->formatDuration($dayTotal) }}
                                    </td>
                                @endforeach
                                <td class="px-6 py-3 whitespace-nowrap text-center text-sm">
                                    {{ $this->formatDuration($this->weeklyData['total']) }}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="{{ count($this->weeklyData['weekDays']) + 2 }}" class="px-6 py-10 text-center text-gray-500">
                                    No time logs for this week
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- List View & Manual Time Log Entry -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @if($view === 'list')
            <div class="lg:col-span-2">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">All Time Logs</h2>
                    <div class="space-y-4">
                        @forelse($timeLogs as $timeLog)
                            <div class="border-b pb-3">
                                <div class="flex justify-between">
                                    <h3 class="font-medium">{{ $timeLog->project->name }}</h3>
                                    <div class="space-x-2">
                                        <button wire:click="startEdit({{ $timeLog->id }})" class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </button>
                                        <button wire:click="deleteTimeLog({{ $timeLog->id }})" 
                                            onclick="confirm('Are you sure you want to delete this time log?') || event.stopImmediatePropagation()"
                                            class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                @if($timeLog->description)
                                    <p class="text-sm text-gray-600">{{ $timeLog->description }}</p>
                                @endif
                                <p class="text-sm text-gray-600">Duration: {{ $timeLog->duration_minutes }} minutes</p>
                                <p class="text-sm text-gray-600">Started: {{ $timeLog->start_time->format('M d, Y H:i') }}</p>
                                @if($timeLog->tags->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($timeLog->tags as $tag)
                                            <span class="px-2 py-1 text-xs rounded-full" 
                                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500">No time logs yet. Start tracking time!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
        
        <div class="{{ $view === 'list' ? '' : 'lg:col-span-3' }} bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Create Manual Time Log</h2>
            <form wire:submit.prevent="save" class="space-y-4">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700">Project</label>
                    <select wire:model="project_id" id="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select a project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="selected_date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" wire:model="selected_date" id="selected_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('selected_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                    <input type="number" wire:model="duration_minutes" id="duration_minutes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('duration_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tags</label>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label 
                                class="inline-flex items-center px-3 py-1 rounded-full cursor-pointer 
                                    {{ in_array($tag->id, $selectedTags) ? 'bg-opacity-100' : 'bg-opacity-30' }}" 
                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}"
                            >
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedTags" 
                                    value="{{ $tag->id }}" 
                                    class="form-checkbox h-4 w-4 mr-1 opacity-0 absolute"
                                >
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Time Log
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Form (shown when editing) -->
    @if($editingTimeLog)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
            <h2 class="text-xl font-semibold mb-4">Edit Time Log</h2>
            <form wire:submit.prevent="updateTimeLog" class="space-y-4">
                <div>
                    <label for="edit_project_id" class="block text-sm font-medium text-gray-700">Project</label>
                    <select wire:model="project_id" id="edit_project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select a project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <textarea wire:model="description" id="edit_description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div>
                    <label for="edit_duration_minutes" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                    <input type="number" wire:model="duration_minutes" id="edit_duration_minutes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('duration_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tags</label>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label 
                                class="inline-flex items-center px-3 py-1 rounded-full cursor-pointer 
                                    {{ in_array($tag->id, $selectedTags) ? 'bg-opacity-100' : 'bg-opacity-30' }}" 
                                style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}"
                            >
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedTags" 
                                    value="{{ $tag->id }}" 
                                    class="form-checkbox h-4 w-4 mr-1 opacity-0 absolute"
                                >
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 border rounded-md hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Update Time Log
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
