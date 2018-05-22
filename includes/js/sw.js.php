<?php
/**
 * Service Worker
 *
 * @package Smart_PWA
 */

?>
'use strict'
const APP_SHELL_CACHE_NAME = 'smart-pwa-<?php echo get_transient( 'smart_pwa_hash' ); ?>'
const RUNTIME_CACHE_NAME = 'smart-pwa-runtime-cache'
const NOT_AVAILABLE_KEY = '<?php echo '/' . user_trailingslashit( get_page_uri( get_option( 'smart_pwa_not_available_page', false ) ) ); ?>'
const MANIFEST_URL = '<?php echo '/' . user_trailingslashit( Smart_PWA\MANIFEST_ENDPOINT ); ?>'

const PRE_CACHE_ASSETS = JSON.parse( '<?php echo json_encode( get_option( 'smart_pwa_assets_paths', [] ) ); ?>' )

const urlsToPreCache = [
	'/',
	MANIFEST_URL,
	NOT_AVAILABLE_KEY,
].concat( PRE_CACHE_ASSETS )

const updateMessage = ( message ) => {
	self.clients.matchAll().then( clients =>
		clients.forEach( client => client.postMessage( message ) ) )
}


self.addEventListener( 'install', ( event ) => {
	console.log( '[ServiceWorker] Install' )
	event.waitUntil(
		caches.open( APP_SHELL_CACHE_NAME )
			.then( ( cache ) => {
				console.log( '[ServiceWorker] Caching app' )
				return cache.addAll( urlsToPreCache )
			} )
	)
} )

self.addEventListener( 'activate', ( event ) => {
	console.log( '[ServiceWorker] Activate' )
	const cacheWhitelist = [APP_SHELL_CACHE_NAME]
	event.waitUntil(
		caches.keys().then( ( cacheNames ) => {
			return Promise.all(
				cacheNames.map( ( cacheName ) => {
					if ( cacheWhitelist.indexOf( cacheName ) === -1 ) {
						console.log( '[ServiceWorker] Removing old cache', cacheName )
						return caches.delete( cacheName )
					}
				} )
			)
		} )
	)
} )


self.addEventListener( 'fetch', ( event ) => {
	if (
		event.request.url.indexOf( 'wp-admin' ) === -1 &&
		event.request.url.indexOf( 'wp-login' ) === -1 &&
		event.request.url.indexOf( 'customize_changeset_uuid' ) === -1 &&
		event.request.method === 'GET'
	) {
		event.respondWith(
			caches.match( event.request ).then( ( responseFromCache ) => {

				//Return static content in cache.
				if ( responseFromCache ) {
					if ( ['style', 'script', 'image'].indexOf( event.request.destination ) > -1 ) {
						return responseFromCache
					}
				}

				//Fetch and save content to cache.
				return fetch( event.request ).then( ( response ) => {
					//Not 200.
					if ( !response || response.status !== 200 || response.type !== 'basic' ) {
						return response
					}

					let responseToCache = response.clone()
					caches.open( RUNTIME_CACHE_NAME ).then( cache => {
						cache.put( event.request, responseToCache )
						console.log( '[ServiceWorker] Fetched&Cached Data', event.request.url )
						updateMessage( {
							key: 'updateContent',
							value: event.request.url
						} )

					} )
					return response

				} ).catch( ( reason ) => {
					//when fetching failed, return content in cache.
					if ( responseFromCache ) {
						console.log( '[ServiceWorker] Cache Matched!', event.request.url, responseFromCache )
						return responseFromCache
					}
					//if not found in cache, return fallback page.
					if ( event.request.mode === 'navigate' ) {
						return caches.match( NOT_AVAILABLE_KEY ).then( ( response ) => {
							return response
						} )
					}
				} )
			} )
		)
	}
} )

