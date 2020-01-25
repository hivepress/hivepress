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

		// Create menu.
		$menu = hp\create_class_instance(
			'\HivePress\Menus\\' . $this->menu,
			[
				[
					'context'    => $this->context,
					'attributes' => $this->attributes,
				],
			]
		);

		if ( $menu ) {

			// Render menu.
			$output .= $menu->render();
		}

		return $output;
	}
}
