export class TimerManager {
    constructor(name) {
        this.name = name;
        this.interval = null;
        this.initialized = false;
        this.cleanupHandlerAdded = false;
        this.timers = new Map(); // Store timer elements and their start times
    }

    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return [
            hours.toString().padStart(2, '0'),
            minutes.toString().padStart(2, '0'),
            secs.toString().padStart(2, '0')
        ].join(':');
    }

    updateTimer(element) {
        try {
            const startTimeStr = element.dataset.start;
            const startTime = startTimeStr.includes('T') 
                ? new Date(startTimeStr) 
                : new Date(startTimeStr.replace(' ', 'T'));
            
            if (isNaN(startTime.getTime())) {
                console.error('Invalid date:', startTimeStr);
                return;
            }

            const now = new Date();
            const diff = Math.floor((now - startTime) / 1000);
            
            // Store the timer element and its start time
            this.timers.set(element, {
                startTime,
                seconds: diff
            });
            
            // Update the display
            element.textContent = this.formatTime(diff);
            
            // Add a pulsing effect when seconds change
            element.classList.add('timer-pulse');
            setTimeout(() => {
                element.classList.remove('timer-pulse');
            }, 500);
            
        } catch (e) {
            console.error('Error updating timer:', e);
        }
    }

    updateAllTimers() {
        document.querySelectorAll('.timer-display').forEach(element => this.updateTimer(element));
    }

    start() {
        if (this.interval) {
            this.stop();
        }
        this.updateAllTimers();
        this.interval = setInterval(() => this.updateAllTimers(), 1000);
        console.log(`Timer ${this.name} started`);
    }

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
            console.log(`Timer ${this.name} stopped`);
        }
    }

    initialize() {
        if (this.initialized) {
            console.log(`Timer ${this.name} already initialized`);
            return;
        }
        
        console.log(`Initializing timer ${this.name}`);
        this.initialized = true;
        
        // Add CSS for timer animations
        this.addStyles();
        
        // Start timers immediately
        this.start();

        // Handle Livewire updates
        document.addEventListener('livewire:update', () => {
            console.log(`Timer ${this.name} handling Livewire update`);
            this.start();
        });

        // Add cleanup handler only once
        if (!this.cleanupHandlerAdded) {
            document.addEventListener('livewire:navigating', () => {
                console.log(`Timer ${this.name} cleaning up`);
                this.stop();
                this.initialized = false;
            });
            this.cleanupHandlerAdded = true;
        }
        
        // Add event listeners for interactive elements
        this.setupEventListeners();
    }
    
    addStyles() {
        // Add CSS for timer animations if not already present
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
                
                /* Form field focus effects */
                .timer-input {
                    transition: border-color 0.2s ease, box-shadow 0.2s ease;
                }
                
                .timer-input:focus {
                    border-color: #6366f1;
                    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
                }
            `;
            document.head.appendChild(style);
        }
    }
    
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
        
        // Add animation to start/stop buttons
        document.querySelectorAll('button[type="submit"], button[wire\\:click*="stopTimer"]').forEach(button => {
            button.classList.add('timer-btn');
        });
        
        // Add enhanced focus effects to form inputs
        document.querySelectorAll('input, textarea').forEach(input => {
            input.classList.add('timer-input');
        });
    }
    
    // Helper method to format time in a human-readable way
    formatTimeHuman(seconds) {
        if (seconds < 60) {
            return `${seconds} seconds`;
        } else if (seconds < 3600) {
            const minutes = Math.floor(seconds / 60);
            return `${minutes} minute${minutes !== 1 ? 's' : ''}`;
        } else {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            if (minutes === 0) {
                return `${hours} hour${hours !== 1 ? 's' : ''}`;
            } else {
                return `${hours} hour${hours !== 1 ? 's' : ''} ${minutes} minute${minutes !== 1 ? 's' : ''}`;
            }
        }
    }
}