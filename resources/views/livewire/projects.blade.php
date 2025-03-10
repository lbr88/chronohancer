<div>
    <h1 class="text-2xl font-semibold mb-6">Projects</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">All Projects</h2>
                <div class="space-y-4">
                    @forelse($projects as $project)
                        <div class="border-b pb-3">
                            <h3 class="font-medium">{{ $project->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $project->description }}</p>
                            @if($project->tags->count() > 0)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($project->tags as $tag)
                                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs rounded">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="flex mt-2">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-3">Start Timer</a>
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No projects yet. Create your first project!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Create New Project</h2>
            <form wire:submit.prevent="save" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Project Name</label>
                    <input type="text" wire:model="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tags</label>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}" class="rounded border-gray-300 text-indigo-600">
                                <span class="ml-2">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
