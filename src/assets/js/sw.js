/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

// This is the "Offline page" service worker
const CACHE = "v2-1";

const offlineFallbackPage = "/panel/offline.html";
const loading_lottie = "/assets/images/logo_lottie.json";

// Install stage sets up the offline page in the cache and opens a new cache
self.addEventListener("install", function (event) {
  console.log("Install Event processing");

  event.waitUntil(
    caches.open(CACHE).then(function (cache) {
      console.log("Cached offline page during install");
      return cache.addAll([ offlineFallbackPage, loading_lottie ]);
    })
  );
});

// If any fetch fails, it will show the offline page.
self.addEventListener("fetch", function (event) {
  if (event.request.method !== "GET") return;

    // 如果logo_lottie.json優先從快取中加載
    if (/^.+\/assets\/images\/logo_lottie.json$/.test(event.request.url)){
        event.respondWith(
            caches.open(CACHE).then(function (cache){
                console.debug("Serving lottie page from cache");
                return cache.match(loading_lottie).then((response) => response || fetch(event.request));
            })
        )
        return;
    }

  event.respondWith(
    fetch(event.request).catch(function (error) {
      // The following validates that the request was for a navigation to a new document
      if (
        event.request.destination !== "document" ||
        event.request.mode !== "navigate"
      ) {
        return;
      }

      console.error("Network request Failed. Serving offline page " + error);
      return caches.open(CACHE).then(function (cache) {
        return cache.match(offlineFallbackPage);
      });
    })
  );
});

// This is an event that can be fired from your page to tell the SW to update the offline page
self.addEventListener("refreshOffline", function () {
  const offlinePageRequest = new Request(offlineFallbackPage);

  return fetch(offlineFallbackPage).then(function (response) {
    return caches.open(CACHE).then(function (cache) {
      console.log("Offline page updated from refreshOffline event: " + response.url);
      return cache.put(offlinePageRequest, response);
    });
  });
});
