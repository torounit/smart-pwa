<?php
/**
 * Bootstrap.
 *
 * @package Smart_PWA
 */

namespace Smart_PWA;

const SW_ENDPOINT            = 'sw';
const MANIFEST_ENDPOINT      = 'manifest';
const UPDATE_CACHE_QUERY_VAR = 'smart-pwa-update';
const CACHE_TIME             = MINUTE_IN_SECONDS * 30;

/**
 * Class App
 */
class App {

	const UPDATE_REWRITE_RULES = 'smart_pwa_queue_flush_rules';

	/**
	 * App constructor.
	 */
	public function __construct() {
		register_activation_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );
		register_deactivation_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );
		register_uninstall_hook( SMART_PWA_FILE, [ __CLASS__, 'queue_flush_rules' ] );

		register_activation_hook( SMART_PWA_FILE, [ __CLASS__, 'remove_transient' ] );
		add_action( 'after_switch_theme', [ __CLASS__, 'after_switch_theme' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'check_and_update_static_cache' ], 9999 );

		add_action( 'plugins_loaded', [ $this, 'init' ] );
		add_filter( 'get_avatar_url', [ $this, 'convert_https_avatar_url' ] );
		add_action( 'wp_head', [ $this, 'register_pwa' ] );
	}

	/**
	 * Gravatar always https.
	 *
	 * @param string $url gravatar url.
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
		<meta name="theme-color" content="<?php echo sanitize_hex_color( get_option( 'smart_pwa_theme_color', '#ffffff' ) ); ?>">
		<link rel="manifest" href="<?php echo home_url( MANIFEST_ENDPOINT ); ?>">
		<script>
			navigator.serviceWorker.register( '<?php echo $endpoint; ?>', { scope: '/' } )
			;
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

	/**
	 * Check and call Update Pre Cache.
	 */
	public static function check_and_update_static_cache() {
		if ( ! get_transient( 'smart_pwa_hash' ) ) {
			self::update_static_cache();
		}
	}

	/**
	 * Update Caches.
	 */
	public static function update_static_cache() {
		$seeker = new Assets_Seeker();
		$assets = $seeker->get_assets();

		if ( has_header_image() ) {
			$assets[] = esc_url( get_header_image() );
		}
		if ( has_custom_logo() ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$assets[]       = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		}
		if ( has_site_icon() ) {
			$assets[] = esc_url( get_site_icon_url() );
		}

		update_option( 'smart_pwa_assets_paths', $assets );
		set_transient( 'smart_pwa_hash', md5( serialize( $assets ) ), CACHE_TIME );
	}

	/**
	 * After switch themes.
	 */
	public static function after_switch_theme() {
		delete_transient( 'smart_pwa_hash' );
	}

	/**
	 * Remove cache hash.
	 */
	public static function remove_transient() {
		delete_transient( 'smart_pwa_hash' );
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
