/* Service worker — cabinet-v1
 * Mis en cache : uniquement les fichiers statiques (/assets/) et la page hors ligne.
 * Les pages de l'application (données comptables, sessions) ne sont JAMAIS mises en cache. */
const VERSION = 'cabinet-v1';
const STATIC_CACHE = VERSION + '-static';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/assets/favicon.svg', '/assets/icons/icon-192.png', '/assets/css/bootstrap.min.css', '/assets/css/style.css'];

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(STATIC_CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Pages : toujours le réseau ; hors ligne -> page dédiée.
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  // Fichiers statiques : cache d'abord, mise à jour en arrière-plan.
  if (url.pathname.startsWith('/assets/')) {
    event.respondWith(
      caches.open(STATIC_CACHE).then((cache) =>
        cache.match(req).then((hit) => {
          const refresh = fetch(req).then((res) => { if (res.ok) cache.put(req, res.clone()); return res; }).catch(() => hit);
          return hit || refresh;
        })
      )
    );
  }
  // Tout le reste (exports, téléchargements, API) passe directement au réseau.
});
