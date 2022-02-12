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
 * Listing submission steps.
 */
class Listing_Submit extends Menu {

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
					'listing_submit'          => [
						'route'  => 'listing_submit_page',
						'_order' => 0,
					],

					'listing_submit_profile'  => [
						'route'  => 'listing_submit_profile_page',
						'_order' => 5,
					],

					'listing_submit_category' => [
						'route'  => 'listing_submit_category_page',
						'_order' => 10,
					],

					'listing_submit_details'  => [
						'route'  => 'listing_submit_details_page',
						'_order' => 20,
					],

					'listing_submit_complete' => [
						'route'  => 'listing_submit_complete_page',
						'_order' => 1000,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
