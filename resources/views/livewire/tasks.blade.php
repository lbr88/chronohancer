<div>
    <h1 class="text-2xl font-semibold mb-6">Tasks</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">All Tasks</h2>
                <div class="space-y-4">
                    @forelse($tasks as $task)
                        <div class="border-b pb-3">
                            <h3 class="font-medium">{{ $task->title }}</h3>
                            <p class="text-sm text-gray-600">{{ $task->description }}</p>
                            <p class="text-sm text-gray-600">Project: {{ $task->project->name }}</p>
                            <p class="text-sm text-gray-600">Due: {{ $task->due_date->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-600">Estimated: {{ $task->estimated_time }} hours</p>
                            <div class="flex mt-2">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-3">Start Timer</a>
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No tasks yet. Create your first task!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Create New Task</h2>
            <form wire:submit.prevent="save" class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Task Title</label>
                    <input type="text" wire:model="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

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
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="estimated_time" class="block text-sm font-medium text-gray-700">Estimated Time (hours)</label>
                    <input type="number" wire:model="estimated_time" id="estimated_time" step="0.5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('estimated_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" wire:model="due_date" id="due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
