<?php

namespace Smart_PWA;

class Assets_Seeker {

	private $assets = [];

	public function __construct() {

		global $wp_styles;
		global $wp_scripts;

		$styles  = clone $wp_styles;
		$scripts = clone $wp_scripts;
		$this->ob_start();
		$styles->do_items( false, 0 );
		$scripts->do_items( false, 0 );
		$styles->do_items( false, 1 );
		$scripts->do_items( false, 1 );
		$this->ob_end_and_parse();
	}

	public function ob_start() {
		ob_start();
	}

	public function ob_end_and_parse() {
		echo $html = ob_get_clean();
		$this->parse( $html );
		if ( did_action( 'wp_footer' ) ) {
			do_action( 'smart_pwa_parsed_assets' );
		}
	}


	/**
	 * @param string $html
	 */
	public function parse( $html ) {
		$dom = new \DOMDocument;
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		$xpath = new \DOMXPath( $dom );

		$styles       = $this->query( $xpath, '//link[@rel="stylesheet"]', 'href' );
		$scripts      = $this->query( $xpath, '//script', 'src' );
		$assets       = array_merge( $styles, $scripts );
		var_dump($assets);
		$assets       = array_map( function ( $asset ) {
			if ( false !== strpos( $asset, home_url() ) ) {
				return $asset = str_replace( trailingslashit( home_url() ), '/', $asset );
			}
		}, $assets );
		$this->assets = array_merge( $this->assets, $assets );
	}

	/**
	 * @param \DOMXPath $xpath
	 * @param string $expression
	 * @param string $attribute
	 *
	 * @return array
	 */
	public function query( \DOMXPath $xpath, $expression, $attribute ) {
		$query  = iterator_to_array( $xpath->query( $expression ) );
		$values = array_filter( array_map( function ( \DOMElement $node ) use ( $attribute ) {
			return $node->getAttribute( $attribute );
		}, $query ) );

		return $values;
	}

	/**
	 * @return array
	 */
	public function get_assets() {
		return $this->assets;
	}
}
