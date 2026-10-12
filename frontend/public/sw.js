const SHELL = "nh-shell-v1";
const PAGES = "nh-pages-v1";
const LESSONS = "nh-lessons-v1";
const PRECACHE = ["/offline.html", "/manifest.json", "/brand/logo.jpeg"];
const PAGE_LIMIT = 40;

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches
      .open(SHELL)
      .then((cache) => cache.addAll(PRECACHE))
      .then(() => self.skipWaiting()),
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(keys.filter((key) => ![SHELL, PAGES, LESSONS].includes(key)).map((key) => caches.delete(key))),
      )
      .then(() => self.clients.claim()),
  );
});

self.addEventListener("fetch", (event) => {
  const url = new URL(event.request.url);
  if (event.request.method !== "GET") return;
  if (url.origin !== self.location.origin) return;
  if (url.pathname.startsWith("/api")) return;

  if (url.pathname === "/offline-lesson.json" || url.pathname.startsWith("/offline-media/")) {
    event.respondWith(
      caches.open(LESSONS).then((cache) => cache.match(event.request).then((hit) => hit || fetch(event.request))),
    );
    return;
  }

  if (PRECACHE.includes(url.pathname)) {
    event.respondWith(caches.match(url.pathname).then((hit) => hit || fetch(event.request)));
    return;
  }

  if (event.request.mode === "navigate") {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (response.ok) {
            const copy = response.clone();
            caches.open(PAGES).then((cache) => remember(cache, event.request, copy));
          }
          return response;
        })
        .catch(() => caches.match(event.request).then((hit) => hit || caches.match("/offline.html"))),
    );
    return;
  }

  if (url.pathname.startsWith("/_next/static/") || url.pathname.startsWith("/brand/") || url.pathname.startsWith("/images/")) {
    event.respondWith(
      caches.match(event.request).then((hit) => {
        const network = fetch(event.request)
          .then((response) => {
            if (response.ok) {
              const copy = response.clone();
              caches.open(PAGES).then((cache) => remember(cache, event.request, copy));
            }
            return response;
          })
          .catch(() => hit || caches.match("/offline.html"));
        return hit || network;
      }),
    );
  }
});

async function remember(cache, request, response) {
  await cache.put(request, response);
  const keys = await cache.keys();
  if (keys.length > PAGE_LIMIT) {
    await cache.delete(keys[0]);
  }
}

self.addEventListener("push", (event) => {
  let data = {};
  try {
    data = event.data ? event.data.json() : {};
  } catch (error) {
    data = { title: "Nujum Al-Huda", body: event.data ? event.data.text() : "" };
  }

  event.waitUntil(
    self.registration.showNotification(data.title || "Nujum Al-Huda", {
      body: data.body || "",
      data: { url: data.url || "/" },
    }),
  );
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const target = (event.notification.data && event.notification.data.url) || "/";
  event.waitUntil(self.clients.openWindow(target));
});
