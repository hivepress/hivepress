<?php
/**
 * Menu component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles menus.
 */
final class Menu extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Add account item to header menu.
		add_filter( 'wp_nav_menu_items', [ $this, 'add_account_menu_item' ], 10, 2 );

		parent::__construct( $args );
	}

	/**
	 * Add account item to header menu.
	 *
	 * @param string $items Menu items.
	 * @param object $args Menu arguments.
	 * @return string
	 */
	public function add_account_menu_item( $items, $args ) {
		if ( 'header' === $args->theme_location ) {
			$items .= ( new Blocks\Part(
				[
					'path' => 'user/login/user-login-link',
				]
			) )->render();
		}
		return $items;
	}

}
