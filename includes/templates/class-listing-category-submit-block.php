<?php
/**
 * Listing category submit block template.
 *
 * @template listing_category_submit_block
 * @description Listing category block in submit context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing category submit block template class.
 *
 * @class Listing_Category_Submit_Block
 */
class Listing_Category_Submit_Block extends Listing_Category_View_Block {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'listing_category_image'     => [
						'path' => 'listing-category/submit/block/listing-category-image',
					],

					'listing_category_name_text' => [
						'path' => 'listing-category/submit/block/listing-category-name',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
