/**
 * LACOWE Welfare MIS - Service Worker
 * Enables offline functionality and app-like experience
 */

const CACHE_NAME = 'lacowe-mis-v1.0.0';
const urlsToCache = [
  '/lacowe-welfare-mis/',
  '/lacowe-welfare-mis/index.php',
  '/lacowe-welfare-mis/login.php',
  '/lacowe-welfare-mis/dashboard.php',
  '/lacowe-welfare-mis/assets/css/style.css',
  '/lacowe-welfare-mis/assets/images/icon-192.png',
  '/lacowe-welfare-mis/assets/images/icon-512.png'
];

// Install event - cache resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }

        return fetch(event.request).then(response => {
          // Check if valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // Clone the response
          const responseToCache = response.clone();

          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });

          return response;
        });
      })
      .catch(() => {
        // Return offline page if available
        return caches.match('/lacowe-welfare-mis/offline.html');
      })
  );
});

// Background sync for offline transactions
self.addEventListener('sync', event => {
  if (event.tag === 'sync-transactions') {
    event.waitUntil(syncTransactions());
  }
});

// Push notifications
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'New notification from LACOWE MIS',
    icon: '/lacowe-welfare-mis/assets/images/icon-192.png',
    badge: '/lacowe-welfare-mis/assets/images/icon-72.png',
    vibrate: [200, 100, 200],
    tag: 'lacowe-notification',
    requireInteraction: true
  };

  event.waitUntil(
    self.registration.showNotification('LACOWE Welfare MIS', options)
  );
});

// Notification click
self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil(
    clients.openWindow('/lacowe-welfare-mis/dashboard.php')
  );
});

// Helper function to sync transactions
async function syncTransactions() {
  // Implement sync logic here
  console.log('Syncing offline transactions...');
}
