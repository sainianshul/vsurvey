const Pusher = require('pusher-js');

// Configuration
const APP_KEY = '39e47d55853774809727';
const APP_CLUSTER = 'ap2';
const AUTH_ENDPOINT = 'https://vyapar.rezel.online/api/v1/broadcasting/auth';
const TOKEN_USER_34 = '6|gKD2L6Zdns0pp3S4ZfbIN8KFCxwoiSIAuRW8h2qFe5bb908e';
const USER_ID = 34;

// Enable pusher logging
Pusher.logToConsole = true;

// Initialize Pusher for User 34 (Receiver)
const pusher = new Pusher(APP_KEY, {
    cluster: APP_CLUSTER,
    authEndpoint: AUTH_ENDPOINT,
    auth: {
        headers: {
            'Authorization': 'Bearer ' + TOKEN_USER_34,
            'Accept': 'application/json'
        }
    }
});

// Subscribe to private channel of User 34
const channel = pusher.subscribe(`private-vyaparmitra_user.${USER_ID}`);

channel.bind('pusher:subscription_succeeded', function() {
    console.log(`\n\n[SUCCESS] Subscribed to private-vyaparmitra_user.${USER_ID} successfully!\n`);
    console.log(`Now run the curl command to send a message to Conversation 2 as User 2...`);
});

channel.bind('pusher:subscription_error', function(error) {
    console.error(`\n\n[ERROR] Subscription failed:`, error);
});

// Bind to event
channel.bind('message.sent', function(data) {
    console.log(`\n\n[🎉 EVENT RECEIVED] message.sent payload:`, JSON.stringify(data, null, 2));
});

// Keep process alive
setInterval(() => {}, 1000);
