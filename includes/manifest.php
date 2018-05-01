<?php
$manifest = [
	"name"             => esc_html( get_bloginfo( 'name' ) ),
	"short_name"       => esc_html( get_bloginfo( 'name' ) ),
	"start_url"        => home_url(),
	"display"          => "standalone",
	"background_color" => sanitize_hex_color( get_option( 'smart_pwa_background_color', '#fff' ) ),
	"description"      => esc_html( get_bloginfo( 'description' ) ),
	"theme_color"      => sanitize_hex_color( get_option( 'smart_pwa_theme_color', '#ffffff' ) ),
];

if ( $site_icon = get_option( 'smart_pwa_icon' ) ) {
	$manifest["icons"] = [
		[
			"src"   => wp_get_attachment_image_url( $site_icon, [ 512, 512 ] ),
			"sizes" => "512x512"
		]
	];
} else {
	$manifest["icons"] = [
		[
			"src"   => esc_url( plugins_url( 'wp-logo.png', SMART_PWA_FILE ) ),
			"sizes" => "512x512"
		]
	];
}

echo json_encode( $manifest );
