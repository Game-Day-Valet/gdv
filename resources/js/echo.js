import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Pusher.logToConsole = true;

try {
    console.log('Loading echo.js...');
    console.log('Pusher:', typeof Pusher);
    console.log('Echo:', typeof Echo);
    console.log('Environment variables:', {
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    });

    // Check for required environment variables
    if (!import.meta.env.VITE_PUSHER_APP_KEY || !import.meta.env.VITE_PUSHER_APP_CLUSTER) {
        throw new Error('Missing Pusher environment variables: VITE_PUSHER_APP_KEY or VITE_PUSHER_APP_CLUSTER not defined');
    }

    // Check for CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        throw new Error('CSRF token not found in meta tag');
    }

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        encrypted: true,
        forceTLS: true,
        // authEndpoint removed - using public channels
    });

    console.log('Echo initialized:', window.Echo);
} catch (error) {
    console.error('Error initializing echo.js:', error.message, error.stack);
}
