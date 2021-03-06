<?php
/**
 * Customizer Setting.
 *
 * @package Smart_PWA
 */

namespace Smart_PWA;

/**
 * Class Customizer
 */
class Customizer {

	/**
	 * Customizer constructor.
	 */
	public function __construct() {
		add_action( 'customize_register', [ $this, 'customize_register' ], 11 );
	}

	/**
	 * Customizer settings.
	 *
	 * @param \WP_Customize_Manager $wp_customize WP_Customize_Manager Object.
	 */
	public function customize_register( \WP_Customize_Manager $wp_customize ) {

		$wp_customize->add_section( 'smart_pwa_options', [
			'title'    => __( 'Smart PWA', 'smart-pwa' ),
			'priority' => 200,
		] );

		$wp_customize->add_setting( 'smart_pwa_icon', [
			'default'           => '',
			'type'              => 'option',
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		] );

		$wp_customize->add_control( new \WP_Customize_Cropped_Image_Control( $wp_customize, 'smart_pwa_icon', [
			'label'   => 'PWA Image',
			'section' => 'smart_pwa_options',
			'width'   => 512,
			'height'  => 512,
		] ) );

		$wp_customize->add_setting( 'smart_pwa_background_color', [
			'default'           => '#ffffff',
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		] );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'smart_pwa_background_color', [
			'label'   => 'Background Color',
			'section' => 'smart_pwa_options',
		] ) );

		$wp_customize->add_setting( 'smart_pwa_theme_color', [
			'default'           => '#fff',
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		] );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'smart_pwa_theme_color', [
			'label'   => 'Theme Color',
			'section' => 'smart_pwa_options',
		] ) );

		$wp_customize->add_setting( 'smart_pwa_not_available_page', [
			'sanitize_callback' => 'esc_html',
			'type'              => 'option',
			'transport'         => 'postMessage',
		] );

		$wp_customize->add_control( 'smart_pwa_not_available_page', [
			'label'   => 'smart_pwa_not_available_page',
			'section' => 'smart_pwa_options',
			'type'    => 'dropdown-pages',
		] );

	}
}
