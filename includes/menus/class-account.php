<?php
/**
 * Account menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Account menu class.
 *
 * @class Account
 */
class Account extends Menu {

	/**
	 * Menu name.
	 *
	 * @var string
	 */
	protected static $name;

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
					[
						'route' => 'listing/todo',
						'order' => 10,
					],

					[
						'route' => 'user/settings',
						'order' => 20,
					],

					[
						'label' => esc_html__( 'Sign Out', 'hivepress' ),
						'url'   => wp_logout_url( home_url() ),
						'order' => 100,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}
}
