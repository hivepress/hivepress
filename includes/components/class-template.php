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

			// Render menu.
			add_action( 'storefront_header', [ $this, 'render_menu' ], 31 );

			// Render modals.
			add_action( 'wp_footer', [ $this, 'render_modals' ] );
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
	 * Renders menu.
	 */
	public function render_menu() {
		echo ( new Blocks\Template( [ 'template_name' => 'menu_block' ] ) )->render();
	}

	/**
	 * Renders modals.
	 */
	public function render_modals() {
		echo ( new Blocks\Template( [ 'template_name' => 'modals_block' ] ) )->render();
	}
}
