import './bootstrap';
import '../css/app.css';
import { TimerManager } from './timer-manager';

// Initialize TimerManager when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    window.globalTimerManager = new TimerManager('global');
    window.globalTimerManager.initialize();
});

// Re-initialize on Livewire page loads
document.addEventListener('livewire:navigated', () => {
    if (!window.globalTimerManager) {
        window.globalTimerManager = new TimerManager('global');
    }
    window.globalTimerManager.initialized = false;
    window.globalTimerManager.initialize();
});

// Make TimerManager available globally
window.TimerManager = TimerManager;