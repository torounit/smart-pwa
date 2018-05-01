<?php
$manifest = [
	"name"             => get_bloginfo( 'name' ),
	"short_name"       => get_bloginfo( 'name' ),
	"start_url"        => home_url(),
	"display"          => "standalone",
	"background_color" => get_option( 'smart_pwa_background_color', '#fff' ),
	"description"      => get_bloginfo( 'description' ),
	"theme_color"      => get_option( 'smart_pwa_theme_color', '#fff' ),
];

if ( $site_icon = get_site_icon_url( '512' ) ) {
	$manifest["icons"] = [
		[
			"src"   => get_site_icon_url( '512' ),
			"sizes" => "512x512"
		]
	];
}

echo json_encode( $manifest );
