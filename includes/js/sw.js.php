'use strict';
const APP_SHELL_CACHE_NAME = 'smart-pwa-app-shell-cache-<?php echo get_option( 'smart_pwa_last_updated' )?>';
const RUNTIME_CACHE_NAME = 'smart-pwa-runtime-cache';
const NOT_AVAILABLE_KEY = '<?php echo '/' . user_trailingslashit( get_page_uri( get_option( 'smart_pwa_not_available_page', false ) ) );?>';
const PRE_CACHE_ASSETS = JSON.parse( '<?php echo json_encode( get_option( 'smart_pwa_assets_paths', [] ) );?>' );

const urlsToPreCache = [
	NOT_AVAILABLE_KEY,
].concat( PRE_CACHE_ASSETS );


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
		event.request.url.indexOf( 'customize_changeset_uuid' ) === - 1 &&
		event.request.method === 'GET'
	) {
		console.log( '[ServiceWorker] Fetch', event.request.url );
		// for cache.
		event.respondWith(
			caches.match( event.request ).then( ( responseFromCache ) => {
				//
				if (responseFromCache) {
					if ([ 'style', 'script', 'image' ].indexOf( event.request.destination ) > - 1) {
						console.log( '[ServiceWorker] Cache Matched!', event.request.url, responseFromCache );
						return responseFromCache;
					}
				}

				let promise = fetch( event.request, { cache: 'default' } ).then( ( response ) => {
					if (! response || response.status !== 200 || response.type !== 'basic') {
						return response;
					}
					let responseToCache = response.clone();
					caches.open( RUNTIME_CACHE_NAME ).then( ( cache ) => {
						cache.put( event.request, responseToCache );
						console.log( '[ServiceWorker] Fetched&Cached Data', event.request.url );
						let message = {
							key: 'updateContent',
							value: event.request.url
						};
						self.clients.matchAll().then( clients =>
							clients.forEach( client => client.postMessage( message ) ) );
					} );

					return response;
				} ).catch( ( reason ) => {
					console.log( '[ServiceWorker]', reason );
				} );

				if (responseFromCache) {
					console.log( '[ServiceWorker] Cache Matched!', event.request.url, responseFromCache );
					return responseFromCache;
				}
				else {
					//fallback contents
					if (event.request.mode === 'navigate') {
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

