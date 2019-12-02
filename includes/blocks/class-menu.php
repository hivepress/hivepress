<?php
/**
 * Menu block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Menu block class.
 *
 * @class Menu
 */
class Menu extends Block {

	/**
	 * Menu name.
	 *
	 * @var string
	 */
	protected $menu;

	/**
	 * Menu attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get menu class.
		$menu_class = '\HivePress\Menus\\' . $this->menu;

		if ( class_exists( $menu_class ) ) {

			// Create menu.
			$menu = new $menu_class( [ 'attributes' => $this->attributes ] );

			// Render menu.
			$output .= $menu->render();
		}

		return $output;
	}
}
