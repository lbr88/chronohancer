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
     * Format seconds into HH:MM:SS
     * @param {number} seconds - Total seconds to format
     * @returns {string} - Formatted time string
     */
    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
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
            const startTimeStr = element.dataset.start;
            this.log('Updating timer with start time:', startTimeStr);
            
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
            
            // Update the display
            const formattedTime = this.formatTime(diffSeconds);
            element.textContent = formattedTime;
            
            this.log(`Timer updated: ${element.id || 'unnamed'} - Start: ${startTimeStr}, Diff: ${diffSeconds}s, Display: ${formattedTime}`);
            
            // Add a pulsing effect when seconds change
            element.classList.add('timer-pulse');
            setTimeout(() => {
                element.classList.remove('timer-pulse');
            }, 500);
            
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
                
                @keyframes timer-pulse {
                    0% { opacity: 1; }
                    50% { opacity: 0.6; }
                    100% { opacity: 1; }
                }
                
                .timer-display {
                    transition: color 0.3s ease;
                    font-family: monospace;
                    font-weight: 600;
                }
                
                /* Timer card hover effects */
                .timer-card {
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }
                
                .timer-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                }
                
                /* Button animations */
                .timer-btn {
                    transition: all 0.2s ease;
                }
                
                .timer-btn:active {
                    transform: scale(0.95);
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
    }
    
    /**
     * Set up mutation observer to detect DOM changes
     */
    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            let hasTimerElements = false;
            
            for (const mutation of mutations) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Check if any timer elements were added
                    hasTimerElements = Array.from(mutation.addedNodes).some(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            return node.classList?.contains('timer-display') ||
                                   node.querySelector?.('.timer-display');
                        }
                        return false;
                    });
                    
                    if (hasTimerElements) break;
                }
            }
            
            if (hasTimerElements) {
                this.log('New timer elements detected, updating timers');
                this.start();
            }
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
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