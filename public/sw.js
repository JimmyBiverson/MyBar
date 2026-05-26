const CACHE_NAME = 'mybar-pos-v1';
const CORE_ROUTES = ['/', '/login', '/dashboard', '/pos'];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(CORE_ROUTES);
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => name !== CACHE_NAME).map(name => caches.delete(name))
            );
        })
    );
});

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET') return;

    if (url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|eot)$/)) {
        event.respondWith(
            caches.match(request).then(cached => cached || fetch(request).then(response => {
                return caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, response.clone());
                    return response;
                });
            }))
        );
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).then(response => {
                return caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, response.clone());
                    return response;
                });
            }).catch(() => {
                return caches.match(request).then(cached => {
                    if (cached) return cached;
                    return new Response(
                        '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline - MyBar POS</title><style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f8f9fa;color:#333;text-align:center}.card{background:#fff;padding:3rem;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.1)}.icon{font-size:4rem;margin-bottom:1rem}h1{margin-bottom:.5rem}p{color:#666;margin-bottom:1.5rem}.btn{background:#7367f0;color:#fff;border:none;padding:.75rem 2rem;border-radius:8px;font-size:1rem;cursor:pointer}.btn:hover{background:#5e50ee}</style></head><body><div class="card"><div class="icon">&#128247;</div><h1>You\'re Offline</h1><p>Please check your internet connection and try again.</p><button class="btn" onclick="location.reload()">Retry</button></div></body></html>',
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                });
            })
        );
        return;
    }

    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
