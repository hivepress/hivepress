<?php
/**
 * Debug component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Debug component class.
 *
 * @class Debug
 */
final class Debug {

	/**
	 * Array of styles.
	 *
	 * @var array
	 */
	private $styles = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Filter styles.
		add_action( 'hivepress/v1/styles', [ $this, 'filter_styles' ] );

		// Enqueue styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ], 99 );

		// Filter scripts.
		add_action( 'hivepress/v1/scripts', [ $this, 'filter_scripts' ] );
	}

	/**
	 * Filters styles.
	 *
	 * @param array $styles Styles.
	 * @return array
	 */
	public function filter_styles( $styles ) {
		foreach ( array_filter(
			$styles,
			function( $style ) {
				return ! is_admin() xor hp\get_array_value( $style, 'admin', false );
			}
		) as $style_name => $style ) {
			if ( isset( $style['src'] ) && ( strpos( $style['src'], 'backend.min.css' ) || strpos( $style['src'], 'frontend.min.css' ) ) ) {
				$this->styles[] = $style;

				unset( $styles[ $style_name ] );
			}
		}

		return $styles;
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {
		$output = '';

		// Enqueue styles.
		foreach ( $this->styles as $style ) {
			$output .= '<link rel="stylesheet/less" type="text/css" href="' . esc_url( str_replace( 'min.css', 'less', $style['src'] ) ) . '" />';
		}

		// Enqueue LESS.
		$output .= '<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js" ></script>';

		echo $output;
	}

	/**
	 * Filters scripts.
	 *
	 * @param array $scripts Scripts.
	 * @return array
	 */
	public function filter_scripts( $scripts ) {
		foreach ( $scripts as $script_name => $script ) {
			if ( isset( $script['src'] ) && ( strpos( $script['src'], 'backend.min.js' ) || strpos( $script['src'], 'frontend.min.js' ) ) ) {
				$scripts[ $script_name ]['src'] = str_replace( 'min.js', 'js', $scripts[ $script_name ]['src'] );
			}
		}

		return $scripts;
	}
}
