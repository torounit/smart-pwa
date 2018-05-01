<?php

namespace Smart_PWA;

const SW_ENDPOINT            = 'sw';
const MANIFEST_ENDPOINT      = 'manifest';
const UPDATE_CACHE_QUERY_VAR = 'smart-pwa-update';

class App {

	const UPDATE_REWRITE_RULES = 'smart_pwa_queue_flush_rules';

	/**
	 * App constructor.
	 */
	public function __construct() {
		register_activation_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );
		register_activation_hook( SMART_PWA_FILE, [ __CLASS__, 'init_static_cache' ] );
		register_deactivation_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );
		register_uninstall_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );

		add_action( 'plugins_loaded', [ $this, 'init' ] );
		add_action( 'after_switch_theme', [ __CLASS__, 'init_static_cache' ] );
		add_action( 'wp_head', [ $this, 'register_pwa' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'update_static_cache' ], 9999 );


		add_filter( 'get_avatar_url', [ $this, 'convert_https_avatar_url' ] );

	}

	/**
	 * Gravatar always https.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function convert_https_avatar_url( $url ) {
		return preg_replace( '/http:\/\/[0-9]\.gravatar\.com/', 'https://secure.gravatar.com', $url );

	}

	/**
	 * Register service worker.
	 */
	public function register_pwa() {

		$endpoint = '/' . trailingslashit( SW_ENDPOINT );
		?>
		<meta name="theme-color"
		      content="<?php echo sanitize_hex_color( get_option( 'smart_pwa_theme_color', '#ffffff' ) ); ?>">
		<link rel="manifest" href="<?php echo home_url( MANIFEST_ENDPOINT ); ?>">
		<script>
			navigator.serviceWorker.register( '<?php echo $endpoint;?>', { scope: '/' } );
		</script>
		<?php
	}

	/**
	 * Update rewrite rules.
	 */
	public static function dequeue_flush_rules() {
		if ( get_option( self::UPDATE_REWRITE_RULES ) ) {
			flush_rewrite_rules();
			update_option( self::UPDATE_REWRITE_RULES, 0 );
		}
	}

	/**
	 * Enqueue update rewrite rules.
	 */
	public static function queue_flush_rules() {
		update_option( self::UPDATE_REWRITE_RULES, 1 );
	}

	public static function init_static_cache() {
		update_option( 'smart_pwa_enqueue_update', 1 );
		wp_remote_get( add_query_arg( UPDATE_CACHE_QUERY_VAR, '1', home_url() ), [ 'timeout' => 120 ] );
	}

	public static function update_static_cache() {
		if ( ! is_admin() && get_option( 'smart_pwa_enqueue_update' ) ) {
			$seeker = new Assets_Seeker();
			update_option( 'smart_pwa_assets_paths', $seeker->get_assets() );
			update_option( 'smart_pwa_last_updated', current_time( 'U' ) );
			update_option( 'smart_pwa_enqueue_update', 0 );
		}

	}

	/**
	 * Initialize
	 */
	public function init() {
		new Controller();
		new Customizer();
		add_action( 'wp_loaded', [ __CLASS__, 'dequeue_flush_rules' ], 200 );
	}


}
