'use strict';

const CACHE = @json($cacheKey);
const OFFLINE_URL = @json($offlineUrl);
const PRECACHE = @json($precacheUrls);

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(PRECACHE.filter(Boolean))).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    const isStaticAsset =
        url.pathname.includes('/build/') ||
        /\.(js|css|png|jpe?g|webp|svg|ico|woff2?|ttf|eot)$/i.test(url.pathname);

    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }

                return fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                    }

                    return response;
                });
            })
        );

        return;
    }

    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => response)
                .catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL)))
        );
    }
});
