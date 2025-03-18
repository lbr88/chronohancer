<div class="relative" x-data="{ open: @entangle('showDropdown') }" @click.away="$wire.closeDropdown()">
  <div class="relative">
    <input
      type="text"
      wire:model.live="projectName"
      wire:click="toggleDropdown"
      placeholder="Select or create a project"
      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm px-3 py-2">
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
  </div>

  @if($showDropdown)
  <div class="absolute z-10 mt-1 w-full bg-white dark:bg-zinc-800 shadow-lg rounded-md py-1 max-h-60 overflow-auto">
    @if($projects->isEmpty())
    <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
      No projects found
    </div>
    @else
    @foreach($projects as $project)
    <div
      wire:key="project-{{ $project->id }}"
      wire:click="selectProject({{ $project->id }}, '{{ addslashes($project->name) }}')"
      class="px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer">
      <div class="font-medium text-gray-900 dark:text-white">
        {{ $project->name }}
      </div>
    </div>
    @endforeach
    @endif

    @if($createNewProject)
    <div
      wire:click="createProject"
      class="px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700 cursor-pointer border-t border-gray-200 dark:border-gray-700">
      <div class="font-medium text-indigo-600 dark:text-indigo-400">
        Create "{{ $projectName }}"
      </div>
    </div>
    @endif
  </div>
  @endif

  <input type="hidden" name="project_id" wire:model="projectId">
</div>