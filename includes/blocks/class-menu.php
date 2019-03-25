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
	protected $menu_name;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$output = ( new \HivePress\Menus\Account() )->render();

		return $output;
	}
}
