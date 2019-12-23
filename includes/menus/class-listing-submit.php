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
	 * Menu meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class initializer.
	 *
	 * @param array $args Menu arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'chained' => true,
				],
			],
			$args
		);

		parent::init( $args );
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
					'listing_submit_page'          => [
						'route'  => 'listing_submit_page',
						'_order' => 10,
					],

					'listing_submit_category_page' => [
						'route'  => 'listing_submit_category_page',
						'_order' => 20,
					],

					'listing_submit_details_page'  => [
						'route'  => 'listing_submit_details_page',
						'_order' => 30,
					],

					'listing_submit_complete_page' => [
						'route'  => 'listing_submit_complete_page',
						'_order' => 100,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
