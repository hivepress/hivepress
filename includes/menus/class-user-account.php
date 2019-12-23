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
	 * Menu meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [
					'user_edit_settings_page' => [
						'route'  => 'user_edit_settings_page',
						'_order' => 50,
					],

					'user_logout_action'      => [
						'label'  => esc_html__( 'Sign Out', 'hivepress' ),
						'url'    => wp_logout_url( home_url( '/' ) ),
						'_order' => 100,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
