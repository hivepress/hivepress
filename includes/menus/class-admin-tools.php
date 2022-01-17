<?php
/**
 * Admin tools menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin tools menu class.
 *
 * @class Admin_Tools
 */
class Admin_Tools extends Menu {

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [
					'admin_tools_page' => [
						'route'  => 'admin_tools_page',
						'_order' => 0,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
