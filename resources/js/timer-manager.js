class TimerManager {
    constructor(name) {
        this.name = name;
        this.interval = null;
        this.initialized = false;
        this.cleanupHandlerAdded = false;
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
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;
            element.textContent = [
                hours.toString().padStart(2, '0'),
                minutes.toString().padStart(2, '0'),
                seconds.toString().padStart(2, '0')
            ].join(':');
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
    }
}