<?php
/**
 * User account menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User account menu class.
 *
 * @class User_Account
 */
class User_Account extends Menu {

	/**
	 * Menu items.
	 *
	 * @var array
	 */
	protected static $items = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Menu arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [
					'user_edit_settings_page' => [
						'route' => 'user_edit_settings_page',
						'order' => 50,
					],

					'user_logout_action'      => [
						'label' => esc_html__( 'Sign Out', 'hivepress' ),
						'url'   => wp_logout_url( home_url( '/' ) ),
						'order' => 100,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}
}
