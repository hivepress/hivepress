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
	 * Chained flag.
	 *
	 * @var bool
	 */
	protected static $chained = false;

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
				'chained' => true,

				'items'   => [
					'listing_submit_page'          => [
						'route' => 'listing_submit_page',
						'order' => 10,
					],

					'listing_submit_category_page' => [
						'route' => 'listing_submit_category_page',
						'order' => 20,
					],

					'listing_submit_details_page'  => [
						'route' => 'listing_submit_details_page',
						'order' => 30,
					],

					'listing_submit_complete_page' => [
						'route' => 'listing_submit_complete_page',
						'order' => 40,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}
}
