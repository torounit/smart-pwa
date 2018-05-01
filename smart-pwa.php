<?php
/**
 * Plugin Name:     Smart PWA
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     smart-pwa
 * Domain Path:     /languages
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





