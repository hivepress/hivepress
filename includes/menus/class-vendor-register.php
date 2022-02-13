<?php
/**
 * Vendor register menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor registration steps.
 */
class Vendor_Register extends Menu {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'chained' => true,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [
					'vendor_register'          => [
						'route'  => 'vendor_register_page',
						'_order' => 0,
					],

					'vendor_register_profile'  => [
						'route'  => 'vendor_register_profile_page',
						'_order' => 10,
					],

					'vendor_register_complete' => [
						'route'  => 'vendor_register_complete_page',
						'_order' => 1000,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
