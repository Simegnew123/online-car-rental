// =====================================================
// Service Worker — Car Rental GPS PWA
// =====================================================

const CACHE_NAME = 'carrental-v2';
const APP_PREFIX = '/online-car-rental/';

// Core assets to cache for offline use
const CORE_ASSETS = [
    APP_PREFIX,
    APP_PREFIX + 'index.php',
    APP_PREFIX + 'cars.php',
    APP_PREFIX + 'manifest.json',
    APP_PREFIX + 'assets/css/style.css',
    APP_PREFIX + 'assets/js/main.js',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
];

// Install — cache core assets
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(CORE_ASSETS).catch(() => {});
        })
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            );
        })
    );
    self.clients.claim();
});

// Fetch — network-first for pages, cache-first for assets
self.addEventListener('fetch', e => {
    if (!e.request.url.startsWith(self.location.origin) &&
        !e.request.url.startsWith('https://unpkg.com')) return;

    if (e.request.method !== 'GET') return;

    // Skip API calls and admin actions
    if (e.request.url.includes('/api/') || e.request.url.includes('action=')) return;

    e.respondWith(
        fetch(e.request).then(response => {
            // Cache successful GET responses
            if (response.ok) {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(e.request, clone));
            }
            return response;
        }).catch(() => {
            // Fallback to cache
            return caches.match(e.request).then(cached => {
                if (cached) return cached;
                // Offline fallback for navigation requests
                if (e.request.mode === 'navigate') {
                    return caches.match(APP_PREFIX + 'index.php');
                }
            });
        })
    );
});
