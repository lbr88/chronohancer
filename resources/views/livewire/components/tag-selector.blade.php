<div
  class="tag-selector"
  x-data="{ 
        showSuggestions: @entangle('showSuggestions'),
        init() {
            // Close suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                    this.showSuggestions = false;
                    @this.closeSuggestions();
                }
            });
        }
    }"
  @click.away="showSuggestions = false">
  <!-- Selected Tags Display -->
  <div class="flex flex-wrap gap-2 mb-2">
    @foreach($tags as $tag)
    <div
      class="inline-flex items-center px-3 py-1 rounded-full"
      style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
      <span>{{ $tag->name }}</span>
      <button
        type="button"
        wire:click="removeTag({{ $tag->id }})"
        class="ml-1.5 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    @endforeach
  </div>

  <!-- Tag Input -->
  <div class="relative">
    <input
      type="text"
      wire:model.live.debounce.300ms="tagInput"
      placeholder="Add tags (comma separated or select from suggestions)"
      class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2"
      @keydown.enter.prevent="$wire.createTag()">

    <!-- Create Tag Button -->
    @if(strlen(trim($tagInput)) > 0)
    <button
      type="button"
      wire:click="createTag"
      class="absolute right-2 top-1/2 transform -translate-y-1/2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
      title="Create new tag">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
    </button>
    @endif

    <!-- Tag Suggestions Dropdown -->
    @if($showSuggestions && $tagSuggestions->isNotEmpty())
    <div class="absolute z-10 mt-1 w-full bg-white dark:bg-zinc-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-auto">
      <ul class="py-1">
        @foreach($tagSuggestions as $tag)
        <li
          wire:click="selectTag({{ $tag->id }}, '{{ $tag->name }}')"
          class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-700 flex items-center">
          <span
            class="w-3 h-3 rounded-full mr-2"
            style="background-color: {{ $tag->color }}"></span>
          <span class="dark:text-white">{{ $tag->name }}</span>
        </li>
        @endforeach
      </ul>
    </div>
    @endif
  </div>

  <!-- Recent Tags -->
  @if($recentTags->isNotEmpty())
  <div class="mt-3">
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Recent tags:</p>
    <div class="flex flex-wrap gap-2">
      @foreach($recentTags as $tag)
      <button
        type="button"
        wire:click="selectTag({{ $tag->id }}, '{{ $tag->name }}')"
        class="inline-flex items-center px-3 py-1 rounded-full hover:ring-2 hover:ring-offset-1 hover:ring-indigo-300"
        style="background-color: {{ $tag->color }}; color: {{ $this->getContrastColor($tag->color) }}">
        {{ $tag->name }}
      </button>
      @endforeach
    </div>
  </div>
  @endif
</div>