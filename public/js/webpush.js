/**
 * Web Push Notification Handler
 */

const WebPush = {
    input: null,
    isSubscribed: false,
    swRegistration: null,

    init() {
        this.input = document.getElementById('push-toggle-input');

        if (this.input) {
            // Sync with the state set by the inline script in admin.blade.php
            this.isSubscribed = this.input.checked;
        }

        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.warn('Push messaging is not supported by your browser');
            if (this.input) this.input.parentElement.style.display = 'none';
            return;
        }

        this.registerServiceWorker();

        if (this.input) {
            this.input.addEventListener('change', () => {
                this.toggleSubscription();
            });
        }
    },

    registerServiceWorker() {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                this.swRegistration = registration;
                this.checkSubscription();
            })
            .catch((error) => {
                console.error('Service Worker registration failed:', error);
            });
    },

    checkSubscription() {
        this.swRegistration.pushManager.getSubscription()
            .then((subscription) => {
                const status = !!subscription;
                
                // Only update and save if status changed
                if (this.isSubscribed !== status) {
                    this.isSubscribed = status;
                    this.updateUI();
                }
                
                if (this.isSubscribed) {
                    this.sendSubscriptionToServer(subscription);
                }
            });
    },

    updateUI() {
        if (!this.input) return;
        this.input.checked = this.isSubscribed;
        localStorage.setItem('push_subscribed', this.isSubscribed);
    },

    toggleSubscription() {
        // Since checkbox already changed state, we check its new state
        if (this.input.checked) {
            this.askPermission();
        } else {
            this.unsubscribeUser();
        }
    },

    askPermission() {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                this.subscribeUser();
            } else {
                this.isSubscribed = false;
                this.updateUI();
                Swal.fire({
                    icon: 'warning',
                    title: 'Quyền thông báo bị chặn',
                    text: 'Vui lòng cho phép thông báo trong cài đặt trình duyệt để nhận tin tức mới nhất.',
                    confirmButtonColor: '#3085d6',
                });
            }
        });
    },

    subscribeUser() {
        const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]').content;
        const applicationServerKey = this.urlBase64ToUint8Array(vapidPublicKey);

        this.swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        })
        .then((subscription) => {
            this.isSubscribed = true;
            this.updateUI();
            this.sendSubscriptionToServer(subscription);
            
            Toast.fire({
                icon: 'success',
                title: 'Đã bật thông báo thành công!'
            });
        })
        .catch((err) => {
            console.error('Failed to subscribe the user: ', err);
            this.isSubscribed = false;
            this.updateUI();
        });
    },

    unsubscribeUser() {
        this.swRegistration.pushManager.getSubscription()
            .then((subscription) => {
                if (subscription) {
                    return subscription.unsubscribe();
                }
            })
            .then(() => {
                this.deleteSubscriptionFromServer();
                this.isSubscribed = false;
                this.updateUI();
                
                Toast.fire({
                    icon: 'info',
                    title: 'Đã tắt thông báo.'
                });
            })
            .catch((error) => {
                console.error('Error unsubscribing', error);
                this.isSubscribed = true;
                this.updateUI();
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
        .catch((error) => {
            console.error('Error sending subscription to server:', error);
        });
    },

    deleteSubscriptionFromServer() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/admin/push-subscriptions', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .catch((error) => {
            console.error('Error deleting subscription from server:', error);
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
