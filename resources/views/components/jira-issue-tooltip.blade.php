<div
    x-data="{
        show: false,
        issue: null,
        mouseX: 0,
        mouseY: 0,
        hideTimeout: null,
        init() {
            this.$watch('show', value => {
                if (!value) {
                    this.mouseX = 0;
                    this.mouseY = 0;
                }
            });
        },
        async fetchIssue(key) {
            if (this.hideTimeout) {
                clearTimeout(this.hideTimeout);
                this.hideTimeout = null;
                if (this.issue) {
                    this.show = true;
                    return;
                }
            }
            try {
                const response = await fetch(`/api/jira/issue/${key}`);
                if (!response.ok) throw new Error('Failed to fetch issue');
                this.issue = await response.json();
                if (!this.hideTimeout) { // Only show if not currently hiding
                    this.show = true;
                }
            } catch (error) {
                console.error('Error fetching Jira issue:', error);
            }
        }
    }"
    x-on:mouseenter="
        if (hideTimeout) clearTimeout(hideTimeout);
        fetchIssue('{{ $issueKey }}');
    "
    x-on:mouseleave="
        hideTimeout = setTimeout(() => {
            show = false;
        }, 150);
    "
    x-on:mousemove="
        if (hideTimeout) clearTimeout(hideTimeout);
        mouseX = $event.clientX;
        mouseY = $event.clientY;
        if (issue && !show) show = true;
    "
    class="relative inline-block"
>
    {{ $slot }}
    
    <div
        x-cloak
        x-show="show && mouseX > 0 && mouseY > 0"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed z-[9999]"
        x-bind:style="(() => {
            const rect = $el.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const tooltipHeight = rect.height;
            let top = mouseY + 10;
            
            // If tooltip would go off bottom of screen, show it above the cursor instead
            if (top + tooltipHeight > viewportHeight) {
                top = mouseY - tooltipHeight - 10;
            }
            
            return {
                left: (mouseX + 10) + 'px',
                top: top + 'px',
                visibility: mouseX > 0 && mouseY > 0 ? 'visible' : 'hidden'
            };
        })()"
        x-cloak
    >
        <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
            <div class="relative bg-white dark:bg-gray-800 p-4" style="min-width: 400px; max-width: 600px;">
                <template x-if="issue">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <img :src="issue.fields.issuetype.iconUrl" :alt="issue.fields.issuetype.name" class="w-4 h-4">
                            <span class="font-medium text-gray-900 dark:text-white break-words whitespace-normal" x-text="issue.fields.summary"></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full" 
                                  :class="{
                                      'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': issue.fields.status.statusCategory.key === 'done',
                                      'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': issue.fields.status.statusCategory.key === 'indeterminate',
                                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': issue.fields.status.statusCategory.key === 'new'
                                  }"
                                  x-text="issue.fields.status.name">
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500 dark:text-gray-400">
                                    Created <span x-text="new Date(issue.fields.created).toLocaleDateString()"></span>
                                </span>
                                <span class="text-gray-400">•</span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    Updated <span x-text="new Date(issue.fields.updated).toLocaleDateString()"></span>
                                </span>
                            </div>
                        </div>
                        <!-- Project and Epic -->
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <span x-text="issue.fields.project.name"></span>
                            <template x-if="issue.fields.customfield_10014">
                                <div class="flex items-center gap-1">
                                    <span class="text-gray-400">•</span>
                                    <span>Epic</span>
                                </div>
                            </template>
                        </div>

                        <!-- Reporter and Assignee -->
                        <div class="flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-1">
                                <span class="text-gray-500">Reporter:</span>
                                <div class="flex items-center gap-1">
                                    <img :src="issue.fields.reporter.avatarUrls['24x24']" class="w-4 h-4 rounded-full">
                                    <span class="text-gray-600 dark:text-gray-300" x-text="issue.fields.reporter.displayName"></span>
                                </div>
                            </div>
                            <template x-if="issue.fields.assignee">
                                <div class="flex items-center gap-1">
                                    <span class="text-gray-500">Assignee:</span>
                                    <div class="flex items-center gap-1">
                                        <img :src="issue.fields.assignee.avatarUrls['24x24']" class="w-4 h-4 rounded-full">
                                        <span class="text-gray-600 dark:text-gray-300" x-text="issue.fields.assignee.displayName"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Labels -->
                        <template x-if="issue.fields.labels && issue.fields.labels.length > 0">
                            <div class="flex flex-wrap gap-1 mt-1">
                                <template x-for="label in issue.fields.labels" :key="label">
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300" x-text="label"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!issue">
                    <div class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-500 dark:text-gray-400">Loading issue details...</span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>