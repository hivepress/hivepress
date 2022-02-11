<?php
/**
 * Listing submit category page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submission page (category selection step).
 */
class Listing_Submit_Category_Page extends Listing_Submit_Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'listing_categories' => [
								'type'    => 'listing_categories',
								'mode'    => 'submit',
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
