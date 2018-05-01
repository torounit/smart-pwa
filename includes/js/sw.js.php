'use strict';
const APP_SHELL_CACHE_NAME = "app-shell-cache-<?php echo get_option( 'pwd_last_updated' )?>";
const RUNTIME_CACHE_NAME = 'runtime-cache';
const  NOT_AVAILABLE_KEY = '/<?php echo NOT_AVAILABLE_ENDPOINT;?>/';

const urlsToPreCache = [
	NOT_AVAILABLE_KEY,
	<?php foreach ( get_option( 'pwa_style_paths' ) as $css ): ?>
	'<?php echo esc_url( $css );?>',
	<?php endforeach; ?>
	<?php foreach ( get_option( 'pwa_script_paths' ) as $js ): ?>
	'<?php echo esc_url( $js );?>',
	<?php endforeach; ?>
];


self.addEventListener( 'install', ( event ) => {
	console.log( '[ServiceWorker] Install' );
	event.waitUntil(
		caches.open( APP_SHELL_CACHE_NAME )
			.then( ( cache ) => {
				console.log( '[ServiceWorker] Caching app' );
				return cache.addAll( urlsToPreCache );
			} )
	);
} );

self.addEventListener( 'activate', ( event ) => {
	console.log( '[ServiceWorker] Activate' );
	const cacheWhitelist = [ APP_SHELL_CACHE_NAME ];
	event.waitUntil(
		caches.keys().then( ( cacheNames ) => {
			return Promise.all(
				cacheNames.map( ( cacheName ) => {
					if (cacheWhitelist.indexOf( cacheName ) === - 1) {
						console.log( '[ServiceWorker] Removing old cache', cacheName );
						return caches.delete( cacheName );
					}
				} )
			);
		} )
	);
} );


self.addEventListener( 'fetch', ( event ) => {
	if (
		event.request.url.indexOf( 'wp-admin' ) === - 1 &&
		event.request.url.indexOf( 'wp-login' ) === - 1 &&
		event.request.method === 'GET'
	) {
		console.log( '[ServiceWorker] Fetch', event.request.url );
		// for cache.
		event.respondWith(
			caches.match( event.request ).then( ( responseFromCache ) => {
				if (responseFromCache) {
					if ([ 'style', 'script', 'image' ].indexOf( event.request.destination ) > - 1) {
						console.log( '[ServiceWorker] Cache Matched!', event.request.url, responseFromCache );
						return responseFromCache;
					}
				}

				let promise = fetch( event.request ).then( ( response ) => {
					if (! response || response.status !== 200 || response.type !== 'basic') {
						return response;
					}
					let responseToCache = response.clone();
					caches.open( RUNTIME_CACHE_NAME ).then( ( cache ) => {
						cache.put( event.request, responseToCache );
						console.log( '[ServiceWorker] Fetched&Cached Data', event.request.url );
						let message = {
							key : 'updateContent',
							value: event.request.url
						};
						self.clients.matchAll().then(clients =>
							clients.forEach(client => client.postMessage(message)));
					} );

					return response;
				} );

				if (responseFromCache) {
					console.log( '[ServiceWorker] Cache Matched!', event.request.url, responseFromCache );
					return responseFromCache;
				}
				else {
					if (event.request.mode === 'navigate') {
						// Follback.
						return caches.match( NOT_AVAILABLE_KEY ).then( ( response ) => {
							return response;
						} );
					}
				}

				return promise;

			} )
		);

	}


} );

