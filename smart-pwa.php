<?php
/**
 * Plugin Name:     Smart PWA
 * Plugin URI:      https://github.com/torounit/smart-pwa
 * Description:     PWA for WordPress
 * Author:          Toro_Unit
 * Author URI:      https://torounit.com
 * Text Domain:     smart-pwa
 * Version:         0.1.0
 * Requires PHP:    7.0
 *
 * @package         Smart_PWA
 */

require dirname( __FILE__ ) . '/includes/autoloader.php';

define( 'SMART_PWA_FILE', __FILE__ );


function smart_pwa_init() {
	new Smart_PWA\App();
}

smart_pwa_init();





