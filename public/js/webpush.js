/**
 * Web Push Notification Handler
 */

const WebPush = {
    init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push messaging is not supported by your browser');
            return;
        }

        this.registerServiceWorker();
    },

    registerServiceWorker() {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('Service Worker registered with scope:', registration.scope);
                this.checkSubscription(registration);
            })
            .catch((error) => {
                console.error('Service Worker registration failed:', error);
            });
    },

    checkSubscription(registration) {
        registration.pushManager.getSubscription()
            .then((subscription) => {
                if (subscription) {
                    // Already subscribed, send to server to keep it fresh
                    this.sendSubscriptionToServer(subscription);
                } else {
                    // Not subscribed, ask for permission
                    this.askPermission(registration);
                }
            });
    },

    askPermission(registration) {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                this.subscribeUser(registration);
            } else {
                console.log('Notification permission denied');
            }
        });
    },

    subscribeUser(registration) {
        const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]').content;
        const applicationServerKey = this.urlBase64ToUint8Array(vapidPublicKey);

        registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        })
        .then((subscription) => {
            console.log('User successfully subscribed:', subscription);
            this.sendSubscriptionToServer(subscription);
        })
        .catch((err) => {
            console.error('Failed to subscribe the user: ', err);
        });
    },

    sendSubscriptionToServer(subscription) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/admin/push-subscriptions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(subscription)
        })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then((data) => {
            console.log('Subscription saved on server:', data);
        })
        .catch((error) => {
            console.error('Error sending subscription to server:', error);
        });
    },

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
};

// Start initialization when page loaded
document.addEventListener('DOMContentLoaded', () => {
    WebPush.init();
});
