<div
    x-data="{ 
        show: false,
        message: '',
        type: 'success',
        init() {
            Livewire.on('notify', ({ type, message }) => {
                this.message = message;
                this.type = type;
                this.show = true;
                setTimeout(() => { this.show = false }, 3000);
            });
        }
    }"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed right-4 top-4 z-50"
>
    <div class="rounded-lg p-4 shadow-lg" :class="{
        'bg-green-50 text-green-800': type === 'success',
        'bg-red-50 text-red-800': type === 'error',
        'bg-yellow-50 text-yellow-800': type === 'warning',
        'bg-blue-50 text-blue-800': type === 'info'
    }">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <!-- Success Icon -->
                <template x-if="type === 'success'">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </template>
                <!-- Error Icon -->
                <template x-if="type === 'error'">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </template>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium" x-text="message"></p>
            </div>
        </div>
    </div>
</div>