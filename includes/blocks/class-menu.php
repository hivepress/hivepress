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
		$output = '<nav class="widget widget_nav_menu">
<ul>
<li><a href="http://localhost/hivepress/account/listings/">My Listings</a></li>
<li><a href="http://localhost/hivepress/account/settings/">My Settings</a></li>
</ul>
		</nav>';

		return $output;
	}
}
