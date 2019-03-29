<?php
/**
 * Listing submit menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit menu class.
 *
 * @class Listing_Submit
 */
class Listing_Submit extends Menu {

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
					'submit_details' => [
						'route' => 'listing/submit_details',
						'order' => 10,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}
}
