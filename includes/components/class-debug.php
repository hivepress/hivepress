<?php
/**
 * Debug component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Debug component class.
 *
 * @class Debug
 */
final class Debug {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Enqueue styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {
		$output = '';

		// Get styles.
		$styles = hivepress()->get_config( 'styles' );

		// Filter styles.
		$styles = array_filter(
			$styles,
			function( $style ) {
				return ! is_admin() xor hp_get_array_value( $style, 'admin', false );
			}
		);

		// Enqueue styles.
		foreach ( $styles as $style ) {
			if ( isset( $style['src'] ) && ( strpos( $style['src'], 'backend.min.css' ) !== false || strpos( $style['src'], 'frontend.min.css' ) ) ) {
				$output .= '<link rel="stylesheet/less" type="text/css" href="' . esc_url( str_replace( 'min.css', 'less', $style['src'] ) ) . '" />';
			}
		}

		// Enqueue LESS.
		$output .= '<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js" ></script>';

		echo $output;
	}
}
