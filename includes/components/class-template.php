<?php
/**
 * Template component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template component class.
 *
 * @class Template
 */
final class Template {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( ! is_admin() ) {

			// Add theme class.
			add_filter( 'body_class', [ $this, 'add_theme_class' ] );

			// Remove theme header.
			add_filter( 'twentynineteen_can_show_post_thumbnail', [ $this, 'remove_theme_header' ] );

			// Render header.
			add_action( 'storefront_header', [ $this, 'render_header' ], 31 );

			// Render footer.
			add_action( 'wp_footer', [ $this, 'render_footer' ] );
		}
	}

	/**
	 * Adds theme class.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function add_theme_class( $classes ) {
		return array_merge( $classes, [ 'hp-theme--' . sanitize_key( get_template() ) ] );
	}

	/**
	 * Removes theme header.
	 *
	 * @param bool $display Display property.
	 * @return bool
	 */
	public function remove_theme_header( $display ) {
		if ( is_singular( hp\prefix( array_keys( hivepress()->get_config( 'post_types' ) ) ) ) ) {
			$display = false;
		}

		return $display;
	}

	/**
	 * Renders header.
	 */
	public function render_header() {
		echo ( new Blocks\Template( [ 'template' => 'header_block' ] ) )->render();
	}

	/**
	 * Renders footer.
	 */
	public function render_footer() {
		echo ( new Blocks\Template( [ 'template' => 'footer_block' ] ) )->render();
	}
}
