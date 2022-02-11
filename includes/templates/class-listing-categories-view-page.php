<?php
/**
 * Listing categories view page template.
 *
 * @template listing_categories_view_page
 * @description Listing catrgories page in view context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing categories page in view context.
 */
class Listing_Categories_View_Page extends Page_Wide {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_header'  => [
						'blocks' => [
							'listing_search_form' => [
								'type'   => 'listing_search_form',
								'_order' => 10,
							],
						],
					],

					'page_content' => [
						'blocks' => [
							'listing_categories' => [
								'type'    => 'listing_categories',
								'columns' => 3,
								'_order'  => 10,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
