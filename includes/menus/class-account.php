<?php
/**
 * Account menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Account menu class.
 *
 * @class Account
 */
class Account extends Menu {

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
					'edit_settings' => [
						'route' => 'user/edit_settings',
						'order' => 50,
					],

					'logout_user'   => [
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
