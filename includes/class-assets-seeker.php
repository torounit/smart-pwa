<?php

namespace Smart_PWA;

class Assets_Seeker {

	private $styles = [];
	private $scripts = [];


	public function __construct() {
		add_action( 'wp_head', [ $this, 'ob_start' ], 0 );
		add_action( 'wp_footer', [ $this, 'ob_start' ], 0 );
		add_action( 'wp_head', [ $this, 'ob_end_and_parse' ], 9999 );
		add_action( 'wp_footer', [ $this, 'ob_end_and_parse' ], 9999 );
		add_action( 'shutdown', [ $this, 'save_paths' ] );
	}

	public function ob_start() {
		ob_start();
	}

	public function ob_end_and_parse() {
		echo $html = ob_get_clean();
		$this->parse( $html );
	}


	/**
	 * @param string $html
	 */
	public function parse( $html ) {
		$dom = new \DOMDocument;
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		$xpath = new \DOMXPath( $dom );

		$styles       = $this->query( $xpath, '//link[@rel="stylesheet"]', 'href' );
		$this->styles = array_merge( $this->styles, $styles );

		$scripts       = $this->query( $xpath, '//script', 'src' );
		$this->scripts = array_merge( $this->scripts, $scripts );

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

	public function save_paths() {
		update_option( 'pwa_style_paths', $this->styles );
		update_option( 'pwa_script_paths', $this->scripts );
	}
}
