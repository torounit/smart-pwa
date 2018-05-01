<?php
$manifest = [
	"name"             => get_bloginfo( 'name' ),
	"short_name"       => get_bloginfo( 'name' ),
	"start_url"        => home_url(),
	"display"          => "standalone",
	"background_color" => "#" . get_background_color(),
	"description"      => get_bloginfo( 'description' ),
	"theme_color"      => "#fff",
	"icons"            => [
		[
			"src"   => get_site_icon_url( '512' ),
			"sizes" => "1000x1000"
		]
	],
];
echo json_encode( $manifest );
