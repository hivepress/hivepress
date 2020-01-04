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
class Listing_Category_Submit_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'listing_category_container' => [
						'type'       => 'container',
						'tag'        => 'article',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-listing-category', 'hp-listing-category--submit-block' ],
						],

						'blocks'     => [
							'listing_category_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-listing-category__header' ],
								],

								'blocks'     => [
									'listing_category_image' => [
										'type'   => 'part',
										'path'   => 'listing-category/submit/block/listing-category-image',
										'_order' => 10,
									],
								],
							],

							'listing_category_content' => [
								'type'       => 'container',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing-category__content' ],
								],

								'blocks'     => [
									'listing_category_name' => [
										'type'   => 'part',
										'path'   => 'listing-category/submit/block/listing-category-name',
										'_order' => 10,
									],
								],
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
