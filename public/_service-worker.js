/**
 * EduTech — Service Worker
 * Strategi:
 *   - Aset statis (CSS, JS, font, gambar): Cache First → hemat bandwidth
 *   - Navigasi halaman (HTML): Network First → konten selalu fresh
 *   - API / POST / data dinamis: Network Only → tidak pernah di-cache
 *   - Offline fallback: tampilkan /offline jika navigasi gagal
 *
 * CHANGELOG:
 *   v2 — update logo/ikon baru (Sep 2026):
 *        - bump versi agar SW lama diganti otomatis
 *        - hapus semua cache ikon lama saat activate
 *        - re-fetch semua ikon dengan ?v=2 saat install
 *          sehingga Android/desktop langsung pakai ikon sekolah baru
 *          tanpa user perlu uninstall/reinstall PWA
 */

const APP_VERSION   = 'edutech-v1';          // ← bump = SW lama dihapus, cache baru dibuat
const STATIC_CACHE  = `${APP_VERSION}-static`;
const DYNAMIC_CACHE = `${APP_VERSION}-dynamic`;
const OFFLINE_URL   = '/offline';

// ─── Cache-busting untuk ikon ─────────────────────────────────────────────────
// Saat versi dinaikkan, query string ?v=2 memaksa browser fetch ikon baru
// meski HTTP cache masih menyimpan versi lama.
const ICON_BUST = '?v=2';

// ─── Aset yang di-pre-cache saat install ─────────────────────────────────────
const PRE_CACHE_ASSETS = [
  '/',
  '/offline',
  '/manifest.json' + ICON_BUST,
  '/azures/styles/bootstrap.css',
  '/azures/styles/style.css',
  '/azures/scripts/bootstrap.min.js',
  '/azures/scripts/custom.js',
  '/azures/fonts/css/fontawesome-all.min.css',
  '/azures/app/icons/icon-72x72.png'       + ICON_BUST,
  '/azures/app/icons/icon-96x96.png'       + ICON_BUST,
  '/azures/app/icons/icon-128x128.png'     + ICON_BUST,
  '/azures/app/icons/icon-144x144.png'     + ICON_BUST,
  '/azures/app/icons/icon-152x152.png'     + ICON_BUST,
  '/azures/app/icons/icon-192x192.png'     + ICON_BUST,
  '/azures/app/icons/icon-384x384.png'     + ICON_BUST,
  '/azures/app/icons/icon-512x512.png'     + ICON_BUST,
  '/azures/app/icons/apple-touch-icon.png' + ICON_BUST,
  '/favicon.ico'                           + ICON_BUST,
];

// ─── Pola URL yang TIDAK boleh di-cache ──────────────────────────────────────
const NEVER_CACHE = [
  /\/login/,
  /\/logout/,
  /\/api\//,
  /\/sanctum\//,
  /\/broadcasting\//,
  /\?(?!v=).*$/,    // URL dengan query string KECUALI ?v= (cache-bust kita sendiri)
];

// ─── Helper ───────────────────────────────────────────────────────────────────
function shouldNeverCache(url) {
  return NEVER_CACHE.some(pattern => pattern.test(url));
}

function isNavigationRequest(request) {
  return request.mode === 'navigate' ||
    (request.method === 'GET' &&
      request.headers.get('accept') &&
      request.headers.get('accept').includes('text/html'));
}

function isStaticAsset(url) {
  return /\.(css|js|woff2?|ttf|eot|otf|png|jpg|jpeg|gif|svg|ico|webp)(\?.*)?$/.test(url);
}

// ─── INSTALL: pre-cache aset + semua ikon baru ───────────────────────────────
self.addEventListener('install', event => {
  console.log('[SW] Install — versi:', APP_VERSION);
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => cache.addAll(PRE_CACHE_ASSETS))
      .then(() => {
        console.log('[SW] Pre-cache selesai, skip waiting...');
        return self.skipWaiting(); // aktifkan langsung tanpa tunggu tab ditutup
      })
  );
});

// ─── ACTIVATE: hapus SEMUA cache versi lama ──────────────────────────────────
self.addEventListener('activate', event => {
  console.log('[SW] Activate — hapus cache lama...');
  event.waitUntil(
    caches.keys()
      .then(keys => {
        const toDelete = keys.filter(key =>
          // Hapus semua cache EduTech kecuali yang versi sekarang
          key.startsWith('edutech-') && key !== STATIC_CACHE && key !== DYNAMIC_CACHE
        );
        if (toDelete.length) {
          console.log('[SW] Cache lama dihapus:', toDelete);
        }
        return Promise.all(toDelete.map(key => caches.delete(key)));
      })
      .then(() => self.clients.claim()) // ambil alih semua tab yang terbuka
      .then(() => {
        // Beritahu semua tab bahwa SW baru aktif (opsional: trigger reload)
        return self.clients.matchAll({ type: 'window' }).then(clients => {
          clients.forEach(client => {
            client.postMessage({ type: 'SW_UPDATED', version: APP_VERSION });
          });
        });
      })
  );
});

// ─── FETCH: strategi per jenis request ───────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = request.url;

  if (request.method !== 'GET') return;
  if (!url.startsWith(self.location.origin)) return;
  if (shouldNeverCache(url)) return;

  // ── Strategi 1: Navigasi HTML → Network First + Offline Fallback ──────────
  if (isNavigationRequest(request)) {
    event.respondWith(
      fetch(request)
        .then(response => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
          }
          return response;
        })
        .catch(() =>
          caches.match(request).then(cached => cached || caches.match(OFFLINE_URL))
        )
    );
    return;
  }

  // ── Strategi 2: Aset Statis → Cache First ────────────────────────────────
  if (isStaticAsset(url)) {
    event.respondWith(
      caches.match(request)
        .then(cached => {
          if (cached) return cached;
          return fetch(request).then(response => {
            if (response.ok) {
              const clone = response.clone();
              caches.open(STATIC_CACHE).then(cache => cache.put(request, clone));
            }
            return response;
          });
        })
        .catch(() => {
          if (/\.(png|jpg|jpeg|gif|svg|webp|ico)$/.test(url)) {
            return caches.match('/azures/app/icons/icon-192x192.png' + ICON_BUST);
          }
        })
    );
    return;
  }

  // ── Strategi 3: Lainnya → Network Only ───────────────────────────────────
});

// ─── MESSAGE: terima perintah dari halaman ────────────────────────────────────
self.addEventListener('message', event => {
  if (!event.data) return;

  if (event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }

  if (event.data.type === 'CLEAR_CACHE') {
    caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k))));
    console.log('[SW] Semua cache dihapus via perintah halaman.');
  }

  // Kirim info versi SW aktif ke halaman yang meminta
  if (event.data.type === 'GET_VERSION') {
    event.source.postMessage({ type: 'SW_VERSION', version: APP_VERSION });
  }
});
