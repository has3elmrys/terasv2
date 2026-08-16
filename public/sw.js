self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Deliberately empty: not calling event.respondWith() lets the browser handle
    // every request natively. Intercepting without a fallback turned any failed
    // request into an unhandled rejection and a network-error page.
});
