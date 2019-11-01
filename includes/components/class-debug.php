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
	protected $styles = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Check status.
		if ( ! defined( 'HP_DEBUG' ) || ! HP_DEBUG ) {
			return;
		}

		// Filter styles.
		add_filter( 'hivepress/v1/styles', [ $this, 'filter_styles' ] );
		add_filter( 'hivetheme/v1/styles', [ $this, 'filter_styles' ] );

		// Enqueue styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ], 99 );

		// Filter scripts.
		add_filter( 'hivepress/v1/scripts', [ $this, 'filter_scripts' ] );
		add_filter( 'hivetheme/v1/scripts', [ $this, 'filter_scripts' ] );
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
			$style_url = explode( '/', hp\get_array_value( $style, 'src' ) );

			if ( in_array( end( $style_url ), [ 'style.css', 'frontend.min.css', 'backend.min.css' ], true ) ) {
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
			$style_url = preg_replace( '/(\.min)?\.css$/', '.less', $style['src'] );

			$output .= '<link rel="stylesheet/less" type="text/css" href="' . esc_url( $style_url ) . '" />';
		}

		// Enqueue LESS.
		$output .= '<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js"></script>';

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
			$script_url = explode( '/', hp\get_array_value( $script, 'src' ) );

			if ( in_array( end( $script_url ), [ 'frontend.min.js', 'backend.min.js' ], true ) ) {
				$scripts[ $script_name ]['src'] = preg_replace( '/\.min\.js$/', '.js', $scripts[ $script_name ]['src'] );
			}
		}

		return $scripts;
	}
}
