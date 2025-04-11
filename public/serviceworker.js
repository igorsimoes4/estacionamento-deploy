var CACHE_NAME = 'estacionamento-cache-v1';
var urlsToCache = [
    '/',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
    '/manifest.json'
];

// Cache on install
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function (cache) {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// Clear cache on activate
self.addEventListener('activate', function (event) {
    var cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.map(function (cacheName) {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Serve from Cache
self.addEventListener('fetch', function (event) {
    event.respondWith(
        fetch(event.request)
            .then(function (response) {
                // Verifica se a resposta é válida
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                // Clona a resposta
                var responseToCache = response.clone();

                caches.open(CACHE_NAME)
                    .then(function (cache) {
                        cache.put(event.request, responseToCache);
                    });

                return response;
            })
            .catch(function () {
                // Se a requisição falhar, tenta buscar do cache
                return caches.match(event.request)
                    .then(function (response) {
                        // Se encontrou no cache, retorna
                        if (response) {
                            return response;
                        }
                        // Se não encontrou no cache e é uma requisição de navegação, retorna a página offline
                        if (event.request.mode === 'navigate') {
                            return caches.match('/offline');
                        }
                    });
            })
    );
});