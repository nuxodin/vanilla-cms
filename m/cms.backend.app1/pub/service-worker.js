!function(){
    'use strict';

    const OFFLINE_CACHE = 'cms.app1.offline';
    const OFFLINE_URL   = 'cms.app1.offline.html';

    self.addEventListener('install', function(event) {
        const offlineRequest = new Request(OFFLINE_URL);
        event.waitUntil(
            fetch(offlineRequest).then(function(response) {
                return caches.open(OFFLINE_CACHE).then(function(cache) {
                    return cache.put(offlineRequest, response);
                });
            })
        );
    });

    self.addEventListener('fetch', function(event) {
        if (event.request.method !== 'GET' ) return;
        if (!event.request.headers.get('accept').includes('text/html')) return;
        event.respondWith(
            fetch(event.request).catch(function(e) {
                return caches.open(OFFLINE_CACHE).then(function(cache) {
                    return cache.match(OFFLINE_URL);
                });
            })
        );
    });

}();
