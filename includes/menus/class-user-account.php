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
 * User account pages.
 */
class User_Account extends Menu {

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [
					'user_edit_settings' => [
						'route'  => 'user_edit_settings_page',
						'_order' => 50,
					],

					'user_logout'        => [
						'label'  => esc_html__( 'Sign Out', 'hivepress' ),
						'url'    => hivepress()->router->get_url( 'user_logout_page' ),
						'_order' => 1000,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
