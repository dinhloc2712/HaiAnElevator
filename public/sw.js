// Version - bump this to force SW update
const SW_VERSION = '1.2';

// Force immediate activation - skip waiting phase
self.addEventListener('install', function(event) {
    console.log('[SW] Install v' + SW_VERSION);
    self.skipWaiting();
});

// Take control of all open tabs immediately
self.addEventListener('activate', function(event) {
    console.log('[SW] Activate v' + SW_VERSION);
    event.waitUntil(clients.claim());
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if (!event.data) return;

    const payload = event.data.json();
    console.log('[SW] Push received v' + SW_VERSION + ':', JSON.stringify(payload));

    const title = payload.title || 'Thông báo mới';

    // Extract URL - library puts custom data in payload.data object
    const targetUrl = (payload.data && payload.data.url)
        || payload.action_url
        || payload.url
        || '/admin/dashboard';

    const options = {
        body: payload.body || 'Bạn có thông báo mới từ hệ thống.',
        icon: '/logo.png',
        badge: '/logo.png',
        data: { url: targetUrl },
        vibrate: [100, 50, 100],
        silent: false
    };

    console.log('[SW] Showing notification, target URL:', targetUrl);

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    let url = event.notification.data && event.notification.data.url;
    console.log('[SW] Notification clicked, raw URL:', url);

    // Normalize relative URL to absolute
    if (url && !url.startsWith('http')) {
        url = self.location.origin + (url.startsWith('/') ? '' : '/') + url;
    }

    // Fallback to dashboard
    if (!url) url = self.location.origin + '/admin/dashboard';

    console.log('[SW] Navigating to:', url);

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            // If any tab is open, navigate it to target URL
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('navigate' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            // Otherwise open a new window
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
