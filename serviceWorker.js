const CACHE_NAME = "com.todo-list.webxspark";
const assets = [
    './',
    './index.html',
    './css/style.css',
    'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css',
    'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&amp;display=swap',
    'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
    'https://cdn.webxspark.com/plugins/js/query.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js',
    'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js',
    'https://cdn.webxspark.com/plugins/js/wxp-progress-bar.min.js',
    'https://cdn.webxspark.com/libraries/prod/wxp-keys.js',
    'https://cdn.webxspark.com/libraries/prod/wxp.js',
    './js/script.js'
];

self.addEventListener("fetch", fetchEvent => {
    fetchEvent.respondWith(
        caches.match(fetchEvent.request).then(res => {
            return res || fetch(fetchEvent.request)
        })
    )
})
self.addEventListener("install", installEvent => {
    installEvent.waitUntil(
      caches.open(CACHE_NAME).then(cache => {
        cache.addAll(assets)
      })
    )
  })