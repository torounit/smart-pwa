<?php
/**
 * Smart PWA
 *
 * PWA for WordPress
 *
 * @package         Smart_PWA
 * @version         0.1.6
 */

/**
 * Plugin Name:     Smart PWA
 * Plugin URI:      https://github.com/torounit/smart-pwa
 * Description:     PWA for WordPress
 * Author:          Toro_Unit
 * Author URI:      https://torounit.com
 * Text Domain:     smart-pwa
 * Version:         0.1.6
 * Requires PHP:    7.0
 */

require dirname( __FILE__ ) . '/includes/autoloader.php';

define( 'SMART_PWA_FILE', __FILE__ );

/**
 * Run Plugin.
 */
function smart_pwa_init() {
	new Smart_PWA\App();
}

smart_pwa_init();





