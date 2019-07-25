<?php
/**
 * Asset component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Asset component class.
 *
 * @class Asset
 */
final class Asset {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Add image sizes.
		add_action( 'init', [ $this, 'add_image_sizes' ] );

		// Enqueue styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Filter scripts.
		add_filter( 'script_loader_tag', [ $this, 'filter_script' ], 10, 2 );
	}

	/**
	 * Adds image sizes.
	 */
	public function add_image_sizes() {
		foreach ( hivepress()->get_config( 'image_sizes' ) as $image_size => $image_size_args ) {
			add_image_size( hp\prefix( $image_size ), $image_size_args['width'], hp\get_array_value( $image_size_args, 'height', 9999 ), hp\get_array_value( $image_size_args, 'crop', false ) );
		}
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {

		// Get styles.
		$styles = hivepress()->get_config( 'styles' );

		// Filter styles.
		$styles = array_filter(
			$styles,
			function( $style ) {
				return ! is_admin() xor hp\get_array_value( $style, 'admin', false );
			}
		);

		// Enqueue styles.
		foreach ( $styles as $style ) {
			wp_enqueue_style( $style['handle'], $style['src'], hp\get_array_value( $style, 'deps', [] ), hp\get_array_value( $style, 'version', HP_CORE_VERSION ) );
		}
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {

		// Get scripts.
		$scripts = hivepress()->get_config( 'scripts' );

		// Filter scripts.
		$scripts = array_filter(
			$scripts,
			function( $script ) {
				return ! is_admin() xor hp\get_array_value( $script, 'admin', false );
			}
		);

		// Enqueue scripts.
		foreach ( $scripts as $script ) {
			wp_enqueue_script( $script['handle'], $script['src'], hp\get_array_value( $script, 'deps', [] ), hp\get_array_value( $script, 'version', HP_CORE_VERSION ), hp\get_array_value( $script, 'in_footer', true ) );

			// Add script data.
			if ( isset( $script['data'] ) ) {
				wp_localize_script( $script['handle'], lcfirst( str_replace( ' ', '', ucwords( str_replace( '-', ' ', $script['handle'] ) ) ) ) . 'Data', $script['data'] );
			}
		}
	}

	/**
	 * Filters script HTML.
	 *
	 * @param string $tag Script tag.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public function filter_script( $tag, $handle ) {
		if ( in_array( $handle, wp_list_pluck( hivepress()->get_config( 'scripts' ), 'handle' ), true ) ) {

			// Set attributes.
			$atts = [ 'async', 'defer' ];

			foreach ( $atts as $att ) {
				if ( wp_scripts()->get_data( $handle, $att ) ) {
					$tag = str_replace( '></', ' ' . $att . '></', $tag );
				}
			}
		}

		return $tag;
	}
}
