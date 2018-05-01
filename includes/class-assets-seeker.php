<?php

namespace Smart_PWA;

class Assets_Seeker {

	private $assets = [];

	public function __construct() {

		global $wp_styles;
		global $wp_scripts;

		$styles       = $this->search_dependencies( $wp_styles, $wp_styles->queue );
		$scripts      = $this->search_dependencies( $wp_scripts, $wp_scripts->queue );
		$assets       = array_merge( $styles, $scripts );
		$assets       = array_map( function ( $asset ) {
			if ( 0 === strpos( $asset, '/') ) {
				return $asset;
			}
			if ( false !== strpos( $asset, home_url() ) ) {
				return $asset = str_replace( trailingslashit( home_url() ), '/', $asset );
			}
		}, $assets );
		$this->assets = array_merge( $this->assets, array_filter( array_unique(  $assets ) ) );
	}

	/**
	 * @param \WP_Dependencies $dependencies
	 * @param $handles
	 *
	 * @return array
	 */
	public function search_dependencies( \WP_Dependencies $dependencies, $handles ) {
		$paths = [];
		foreach ( $handles as $handle ) {
			/** @var |_WP_Dependency $asset */
			$asset = $dependencies->registered[ $handle ];
			if ( ! empty( $asset->src ) && is_string( $asset->src ) ) {
				$paths[] = $asset->src;
			}

			if ( ! empty( $asset->deps ) && is_array( $asset->deps ) ) {
				$deps  = $this->search_dependencies( $dependencies, $asset->deps );
				$paths = array_merge( $paths, $deps );
			}
		}

		return $paths;
	}

	/**
	 * @return array
	 */
	public function get_assets() {
		return $this->assets;
	}
}
