<?php
/**
 * Controller.
 *
 * @package Smart_PWA.
 */

namespace Smart_PWA;

/**
 * Class Controller
 */
class Controller {

	/**
	 * Controller constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'template_redirect' ] );
		add_action( 'init', [ $this, 'add_endpoints' ] );
		add_filter( 'query_vars', [ $this, 'query_vars' ] );
	}

	/**
	 * Add query var to white list.
	 *
	 * @param array $vars The array of whitelisted query variable names.
	 *
	 * @return array
	 */
	public function query_vars( $vars ) {
		$vars[] = SW_ENDPOINT;
		$vars[] = MANIFEST_ENDPOINT;
		$vars[] = UPDATE_CACHE_QUERY_VAR;

		return $vars;
	}

	/**
	 * Add endpoint.
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( SW_ENDPOINT, EP_ROOT );
		add_rewrite_endpoint( MANIFEST_ENDPOINT, EP_ROOT );
	}

	/**
	 * Select view.
	 */
	public function template_redirect() {
		/**
		 * Global \WP_Query.
		 *
		 * @var \WP_Query;
		 */
		global $wp_query;

		if ( isset( $wp_query->query[ SW_ENDPOINT ] ) ) {
			header( 'Content-Type: text/javascript' );
			header( 'Cache-Control: max-age=' . CACHE_TIME );
			header( 'Service-Worker-Allowed: /' );
			include dirname( SMART_PWA_FILE ) . '/includes/js/sw.js.php';
			exit;
		}

		if ( isset( $wp_query->query[ MANIFEST_ENDPOINT ] ) ) {
			header( 'Content-Type: application/manifest+json' );
			include dirname( SMART_PWA_FILE ) . '/includes/manifest.php';
			exit;
		}
	}
}
