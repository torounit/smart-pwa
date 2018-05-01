<?php

namespace Smart_PWA;

const SW_ENDPOINT            = 'sw';
const MANIFEST_ENDPOINT      = 'manifest';
const UPDATE_CACHE_QUERY_VAR = 'smart-pwa-update';

class App {

	public function __construct() {

		register_activation_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );
		register_deactivation_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );
		register_uninstall_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );

		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}

	public static function dequeue_flush_rules() {
		if ( get_option( 'queue_flush_rules' ) ) {
			flush_rewrite_rules();
			update_option( 'queue_flush_rules', 0 );
		}
	}

	public static function queue_flush_rules() {
		update_option( 'queue_flush_rules', 1 );
	}

	public function init() {
		new Controller();
		add_action( 'wp_loaded', [ __CLASS__, 'dequeue_flush_rules' ], 200 );
	}


}
