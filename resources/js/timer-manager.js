/**
 * TimerManager - A class to manage and update timer displays
 */
export class TimerManager {
    /**
     * Create a new TimerManager instance
     * @param {string} name - A name for this timer manager instance (for logging)
     */
    constructor(name) {
        this.name = name;
        this.interval = null;
        this.initialized = false;
        this.timers = new Map(); // Store timer elements and their data
        this.debug = true; // Set to true to enable debug logging
    }

    /**
     * Format seconds into the appropriate format
     * @param {number} seconds - Total seconds to format
     * @param {string} format - Format to use (hms, hm, human)
     * @returns {string} - Formatted time string
     */
    formatTime(seconds, format = 'hms') {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        // Default to hms format
        if (!format || format === 'hms') {
            return [
                hours.toString().padStart(2, '0'),
                minutes.toString().padStart(2, '0'),
                secs.toString().padStart(2, '0')
            ].join(':');
        }
        // HH:MM format
        else if (format === 'hm') {
            return [
                hours.toString().padStart(2, '0'),
                minutes.toString().padStart(2, '0')
            ].join(':');
        }
        // Human readable format (e.g., 3h 40m 5s)
        else if (format === 'human') {
            if (hours > 0) {
                if (minutes > 0) {
                    if (secs > 0) {
                        return `${hours}h ${minutes}m ${secs}s`;
                    }
                    return `${hours}h ${minutes}m`;
                }
                return `${hours}h`;
            }
            
            if (minutes > 0) {
                if (secs > 0) {
                    return `${minutes}m ${secs}s`;
                }
                return `${minutes}m`;
            }
            
            return `${secs}s`;
        }
        
        // Fallback to HH:MM:SS
        return [
            hours.toString().padStart(2, '0'),
            minutes.toString().padStart(2, '0'),
            secs.toString().padStart(2, '0')
        ].join(':');
    }

    /**
     * Log debug messages if debug is enabled
     * @param {...any} args - Arguments to log
     */
    log(...args) {
        if (this.debug) {
            console.log(`[TimerManager:${this.name}]`, ...args);
        }
    }

    /**
     * Parse a date string safely
     * @param {string} dateStr - Date string to parse
     * @returns {Date|null} - Parsed Date object or null if invalid
     */
    parseDate(dateStr) {
        if (!dateStr) {
            this.log('Empty date string provided');
            return null;
        }

        try {
            // Try parsing as ISO format first
            let date = new Date(dateStr);
            
            // If invalid, try other formats
            if (isNaN(date.getTime())) {
                // Try MySQL format (YYYY-MM-DD HH:MM:SS)
                if (dateStr.includes(' ')) {
                    date = new Date(dateStr.replace(' ', 'T'));
                }
            }
            
            // Final check if date is valid
            if (isNaN(date.getTime())) {
                this.log('Invalid date after parsing attempts:', dateStr);
                return null;
            }
            
            return date;
        } catch (e) {
            this.log('Error parsing date:', e, dateStr);
            return null;
        }
    }

    /**
     * Update a single timer element
     * @param {HTMLElement} element - The timer display element
     */
    updateTimer(element) {
        try {
            // Get the start time from the data attribute
            const startTimeStr = element.dataset.start;
            this.log('Updating timer with start time:', startTimeStr);
            
            // Parse the start time
            const startTime = this.parseDate(startTimeStr);
            if (!startTime) {
                element.textContent = '00:00:00';
                return;
            }
            
            const now = new Date();
            const diffSeconds = Math.floor((now - startTime) / 1000);
            
            if (diffSeconds < 0) {
                this.log('Negative time difference:', diffSeconds);
                element.textContent = '00:00:00';
                return;
            }
            
            // Store the timer data
            this.timers.set(element, {
                startTime,
                seconds: diffSeconds,
                lastUpdated: now
            });
            
            // Get the time format from the data attribute or use default
            let timeFormat = element.dataset.timeFormat || 'hms';
            
            // Update the display with the format
            const formattedTime = this.formatTime(diffSeconds, timeFormat);
            element.textContent = formattedTime;
            
            this.log(`Timer updated: ${element.id || 'unnamed'} - Start: ${startTimeStr}, Diff: ${diffSeconds}s, Format: ${timeFormat}, Display: ${formattedTime}`);
            
            // Add a pulsing effect when seconds change
            element.classList.add('timer-pulse');
            setTimeout(() => {
                element.classList.remove('timer-pulse');
            }, 500);
            
            // Update the total duration display if it exists
            const timerContainer = element.closest('.flex-col');
            if (timerContainer) {
                const totalDurationElement = timerContainer.querySelector('.text-gray-500');
                if (totalDurationElement && totalDurationElement.textContent.includes('Today:')) {
                    // If we don't have stored total seconds, extract it from the text
                    if (!totalDurationElement.dataset.totalSeconds) {
                        const totalText = totalDurationElement.textContent;
                        const timeMatch = totalText.match(/Today: (\d{2}):(\d{2}):(\d{2})/);
                        
                        if (timeMatch) {
                            const [_, hours, minutes, seconds] = timeMatch;
                            const totalSeconds = parseInt(hours) * 3600 + parseInt(minutes) * 60 + parseInt(seconds);
                            totalDurationElement.dataset.totalSeconds = totalSeconds;
                            totalDurationElement.dataset.lastUpdated = Date.now();
                        }
                    }
                    
                    // If we have stored total seconds, update it
                    if (totalDurationElement.dataset.totalSeconds) {
                        const totalSeconds = parseInt(totalDurationElement.dataset.totalSeconds);
                        const lastUpdated = parseInt(totalDurationElement.dataset.lastUpdated || 0);
                        const now = Date.now();
                        
                        // Only update if timer is running (element has current time)
                        if (diffSeconds > 0) {
                            // Calculate seconds elapsed since last update
                            const elapsedSince = Math.floor((now - lastUpdated) / 1000);
                            
                            // Update the total seconds and last updated time
                            const newTotalSeconds = totalSeconds + elapsedSince;
                            totalDurationElement.dataset.totalSeconds = newTotalSeconds;
                            totalDurationElement.dataset.lastUpdated = now;
                            
                            // Get the time format from the main timer element's data attribute
                            let timeFormat = timerElement.dataset.timeFormat || 'hms';
                            
                            // Update the display with the format
                            totalDurationElement.textContent = `Today: ${this.formatTime(newTotalSeconds, timeFormat)}`;
                            
                            // Add a subtle pulse to the total duration as well
                            totalDurationElement.classList.add('timer-pulse-subtle');
                            setTimeout(() => {
                                totalDurationElement.classList.remove('timer-pulse-subtle');
                            }, 500);
                        }
                    }
                }
            }
            
        } catch (e) {
            this.log('Error updating timer:', e);
            element.textContent = '00:00:00';
        }
    }

    /**
     * Update all timer elements on the page
     */
    updateAllTimers() {
        const timerElements = document.querySelectorAll('.timer-display');
        this.log(`Found ${timerElements.length} timer elements to update`);
        
        if (timerElements.length === 0) {
            this.log('No timer elements found, stopping timer manager');
            this.stop();
            return;
        }
        
        timerElements.forEach(element => this.updateTimer(element));
    }

    /**
     * Start the timer update interval
     */
    start() {
        if (this.interval) {
            this.stop();
        }
        
        this.log('Starting timer updates');
        this.updateAllTimers();
        this.interval = setInterval(() => this.updateAllTimers(), 1000);
    }

    /**
     * Stop the timer update interval
     */
    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
            this.log('Timer updates stopped');
        }
    }

    /**
     * Add CSS styles for timer animations
     */
    addStyles() {
        if (!document.getElementById('timer-manager-styles')) {
            const style = document.createElement('style');
            style.id = 'timer-manager-styles';
            style.textContent = `
                .timer-pulse {
                    animation: timer-pulse 0.5s ease-in-out;
                }
                
                .timer-pulse-subtle {
                    animation: timer-pulse-subtle 0.5s ease-in-out;
                }
                
                @keyframes timer-pulse {
                    0% { opacity: 1; }
                    50% { opacity: 0.6; }
                    100% { opacity: 1; }
                }
                
                @keyframes timer-pulse-subtle {
                    0% { opacity: 1; }
                    50% { opacity: 0.8; }
                    100% { opacity: 1; }
                }
                
                .timer-display {
                    transition: color 0.3s ease;
                    font-family: monospace;
                    font-weight: 600;
                    font-size: 1.25rem;
                    letter-spacing: 0.05em;
                    text-shadow: 0 0 1px rgba(79, 70, 229, 0.2);
                }
                
                /* Timer card hover effects */
                .timer-card {
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }
                
                .timer-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                }
                
                /* Timer display column effects */
                .flex-col.items-center.justify-center {
                    transition: all 0.3s ease;
                    border-radius: 0.5rem;
                    padding: 0.5rem;
                }
                
                .flex-col.items-center.justify-center:hover {
                    background-color: rgba(79, 70, 229, 0.1);
                }
                
                /* Button animations */
                .timer-btn {
                    transition: all 0.2s ease;
                }
                
                .timer-btn:active {
                    transform: scale(0.95);
                }
                
                /* Stop button styles */
                .stop-button {
                    padding: 0 !important;
                }
                
                .stop-button svg {
                    transition: all 0.2s ease;
                    stroke-width: 2.5;
                }
                
                .stop-button:hover svg {
                    transform: scale(1.1);
                }
            `;
            document.head.appendChild(style);
            this.log('Timer styles added');
        }
    }

    /**
     * Set up event listeners for interactive elements
     */
    setupEventListeners() {
        // Add hover effects to timer cards
        document.querySelectorAll('.timer-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                const timerDisplay = card.querySelector('.timer-display');
                if (timerDisplay) {
                    timerDisplay.style.color = '#4f46e5'; // Highlight color on hover
                }
            });
            
            card.addEventListener('mouseleave', () => {
                const timerDisplay = card.querySelector('.timer-display');
                if (timerDisplay) {
                    timerDisplay.style.color = ''; // Reset to default color
                }
            });
        });
        
        // Add animation to buttons
        document.querySelectorAll('button[type="submit"], button[wire\\:click*="stopTimer"]').forEach(button => {
            button.classList.add('timer-btn');
        });
        
        // Add animation to buttons except stop buttons
        document.querySelectorAll('button[type="submit"]').forEach(button => {
            button.classList.add('timer-btn');
        });
        
        // Special handling for stop buttons
        document.querySelectorAll('.stop-button').forEach(button => {
            // Remove any padding
            button.style.padding = '0';
            
            // Ensure the SVG is not modified by other code
            const svg = button.querySelector('svg');
            if (svg) {
                svg.style.pointerEvents = 'none';
                
                // Prevent any other code from modifying this SVG
                svg.setAttribute('data-no-modify', 'true');
                
                // Make sure the SVG is visible
                svg.style.display = 'block';
                svg.style.width = '24px';
                svg.style.height = '24px';
            }
        });
    }

    /**
     * Initialize the timer manager
     */
    initialize() {
        if (this.initialized) {
            this.log('Already initialized, restarting timer');
            this.start();
            return;
        }
        
        this.log('Initializing timer manager');
        this.initialized = true;
        
        // Add CSS for timer animations
        this.addStyles();
        
        // Start timers immediately
        this.start();
        
        // Handle Livewire events
        this.setupLivewireEventListeners();
        
        // Listen for DOM changes that might add new timer elements
        this.setupMutationObserver();
        
        // Add event listeners for interactive elements
        this.setupEventListeners();
        
        this.log('Timer manager initialization complete');
    }
    
    /**
     * Set up Livewire-specific event listeners
     */
    setupLivewireEventListeners() {
        // Handle initial Livewire load
        document.addEventListener('livewire:initialized', () => {
            this.log('Livewire initialized event detected');
            this.start();
        });
        
        // Handle Livewire updates
        document.addEventListener('livewire:update', () => {
            this.log('Livewire update event detected');
            // Small delay to ensure DOM is updated
            setTimeout(() => this.start(), 100);
        });
        
        // Handle navigation start
        document.addEventListener('livewire:navigating', () => {
            this.log('Navigation started, cleaning up');
            this.stop();
            this.initialized = false; // Reset initialization state
        });
        
        // Handle navigation complete
        document.addEventListener('livewire:navigated', () => {
            this.log('Navigation completed, reinitializing');
            // Small delay to ensure DOM is fully updated
            setTimeout(() => {
                this.initialized = false; // Reset to force full initialization
                this.initialize();
            }, 200);
        });
        
        // Handle page load
        document.addEventListener('livewire:load', () => {
            this.log('Livewire load event detected');
            this.initialized = false; // Reset to force full initialization
            this.initialize();
        });
        
        // Handle Alpine.js initialization (if used)
        document.addEventListener('alpine:initialized', () => {
            this.log('Alpine initialized event detected');
            setTimeout(() => this.start(), 100);
        });
        
        // Handle timer started event (including restarts)
        document.addEventListener('timerStarted', (event) => {
            this.log('Timer started/restarted event detected', event.detail);
            
            // Force refresh of all timer elements to get the latest start times
            setTimeout(() => {
                // Clear any cached timer data
                this.timers.clear();
                
                // If we have specific timer ID and start time in the event detail
                if (event.detail && event.detail.timerId) {
                    const timerId = event.detail.timerId;
                    const startTime = event.detail.startTime;
                    
                    // Find the specific timer element that was restarted
                    const timerElement = document.getElementById(`timer-${timerId}`);
                    
                    if (timerElement && startTime) {
                        this.log(`Updating restarted timer ${timerId} with new start time: ${startTime}`);
                        
                        // Update the data-start attribute with the new start time
                        timerElement.dataset.start = startTime;
                        
                        // If we have total duration info in the event, store it for real-time updates
                        if (event.detail.totalDuration) {
                            // Find the total duration element
                            const parentContainer = timerElement.closest('.flex');
                            if (parentContainer) {
                                const totalDurationElement = parentContainer.querySelector('.text-gray-500');
                                if (totalDurationElement && totalDurationElement.textContent.includes('Today:')) {
                                    // Parse the total duration into seconds
                                    const [hours, minutes, seconds] = event.detail.totalDuration.split(':').map(Number);
                                    const totalSeconds = hours * 3600 + minutes * 60 + seconds;
                                    
                                    // Store the total seconds and last updated time
                                    totalDurationElement.dataset.totalSeconds = totalSeconds;
                                    totalDurationElement.dataset.lastUpdated = Date.now();
                                    
                                    // Detect the current time format
                                    let timeFormat = 'hms'; // Default format
                                    const formatButtons = document.querySelectorAll('button[wire\\:click^="setTimeFormat"]');
                                    formatButtons.forEach(button => {
                                        if (button.classList.contains('bg-indigo-600')) {
                                            // Extract format from the wire:click attribute
                                            const clickAttr = button.getAttribute('wire:click');
                                            const formatMatch = clickAttr.match(/setTimeFormat\('([^']+)'\)/);
                                            if (formatMatch && formatMatch[1]) {
                                                timeFormat = formatMatch[1];
                                            }
                                        }
                                    });
                                    
                                    // Format the total duration according to the current format
                                    const formattedDuration = this.formatTime(totalSeconds, timeFormat);
                                    totalDurationElement.textContent = `(Today: ${formattedDuration})`;
                                }
                            }
                        }
                        
                        // Update this specific timer
                        this.updateTimer(timerElement);
                    }
                }
                
                // Re-query all timer elements to get fresh data
                const timerElements = document.querySelectorAll('.timer-display');
                this.log(`Refreshing ${timerElements.length} timer elements after timer start/restart`);
                
                timerElements.forEach(element => {
                    // Log the start time for debugging
                    this.log(`Timer ${element.id || 'unnamed'} start time: ${element.dataset.start}`);
                    this.updateTimer(element);
                });
            }, 100);
        });
        
        // Handle timer stopped event
        document.addEventListener('timerStopped', (event) => {
            this.log('Timer stopped event detected', event.detail);
            // Update the display of all timers
            setTimeout(() => this.updateAllTimers(), 100);
        });
    }
    
    /**
     * Set up mutation observer to detect DOM changes
     */
    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            let hasTimerElements = false;
            let hasAttributeChanges = false;
            
            for (const mutation of mutations) {
                // Check for added timer elements
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    hasTimerElements = Array.from(mutation.addedNodes).some(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            return node.classList?.contains('timer-display') ||
                                   node.querySelector?.('.timer-display');
                        }
                        return false;
                    });
                    
                    if (hasTimerElements) break;
                }
                
                // Check for attribute changes on timer elements (especially data-start)
                if (mutation.type === 'attributes' &&
                    mutation.attributeName === 'data-start' &&
                    mutation.target.classList?.contains('timer-display')) {
                    
                    hasAttributeChanges = true;
                    const element = mutation.target;
                    this.log(`Timer element attribute changed: ${element.id || 'unnamed'}`, {
                        attribute: mutation.attributeName,
                        newValue: element.dataset.start
                    });
                    
                    // Update this specific timer immediately
                    this.updateTimer(element);
                }
            }
            
            if (hasTimerElements) {
                this.log('New timer elements detected, updating all timers');
                // Clear any cached timer data
                this.timers.clear();
                this.start();
            } else if (hasAttributeChanges) {
                this.log('Timer attributes changed, updating affected timers');
                // Individual timers already updated above
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['data-start']
        });
    }
}

// Auto-initialize when the script is loaded in a browser environment
if (typeof window !== 'undefined') {
    // Create a single global instance
    if (!window.globalTimerManager) {
        window.globalTimerManager = new TimerManager('global');
    }
    
    // Initialize on DOM content loaded
    window.addEventListener('DOMContentLoaded', () => {
        console.log('DOM content loaded, initializing global timer manager');
        window.globalTimerManager.initialized = false; // Force reinitialization
        window.globalTimerManager.initialize();
    });
    
    // Initialize on Livewire page navigations
    document.addEventListener('livewire:navigated', () => {
        console.log('Page navigation completed, reinitializing global timer manager');
        setTimeout(() => {
            window.globalTimerManager.initialized = false; // Force reinitialization
            window.globalTimerManager.initialize();
        }, 200);
    });
    
    // Initialize on turbo:load for Turbo Drive (if used)
    document.addEventListener('turbo:load', () => {
        console.log('Turbo load event, reinitializing global timer manager');
        window.globalTimerManager.initialized = false; // Force reinitialization
        window.globalTimerManager.initialize();
    });
}