<?php

namespace Smart_PWA;

class Controller {

	function __construct() {

		add_action( 'template_redirect', [ $this, 'template_redirect' ] );
		add_action( 'init', [ $this, 'add_endpoints' ] );
		add_action( 'query_vars', [ $this, 'query_vars' ] );
	}

	public function query_vars() {
		$vars[] = SW_ENDPOINT;
		$vars[] = MANIFEST_ENDPOINT;

		return $vars;
	}

	public function add_endpoints() {
		add_rewrite_endpoint( SW_ENDPOINT, EP_ROOT );
		add_rewrite_endpoint( MANIFEST_ENDPOINT, EP_ROOT );
	}

	public function template_redirect() {
		/**
		 * @var \WP_Query;
		 */
		global $wp_query;



		if ( isset( $wp_query->query[ SW_ENDPOINT ] ) ) {
			header( 'Content-Type: text/javascript' );
			header( 'Cache-Control: max-age=' . MINUTE_IN_SECONDS * 30 );
			header( 'Service-Worker-Allowed: /' );
			include dirname( SMART_PWA_FILE ) . '/js/service-worker.js.php';
			exit;
		}

		if ( isset( $wp_query->query[ MANIFEST_ENDPOINT ] ) ) {
			header( 'Content-Type: application/manifest+json' );
			include dirname( __FILE__ ) . '/manifest.php';
			exit;
		}

		if ( isset( $wp_query->query[ UPDATE_CACHE_QUERY_VAR ] ) ) {
			new Assets_Seeker();
			update_option( 'pwd_last_updated', current_time( 'U' ) );
		}
	}
}
