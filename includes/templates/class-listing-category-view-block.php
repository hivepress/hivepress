<?php
/**
 * Listing category view block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing category block in view context.
 */
class Listing_Category_View_Block extends Template {

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
							'class' => [ 'hp-listing-category', 'hp-listing-category--view-block' ],
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
										'path'   => 'listing-category/view/block/listing-category-image',
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
										'type'       => 'container',
										'tag'        => 'h4',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-listing-category__name' ],
										],

										'blocks'     => [
											'listing_category_name_text'           => [
												'type'   => 'part',
												'path'   => 'listing-category/view/block/listing-category-name',
												'_order' => 10,
											],
										],
									],

									'listing_category_details_primary' => [
										'type'       => 'container',
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing-category__details', 'hp-listing-category__details--primary' ],
										],

										'blocks'     => [
											'listing_category_count' => [
												'type'   => 'part',
												'path'   => 'listing-category/view/listing-category-item-count',
												'_order' => 10,
											],
										],
									],

									'listing_category_description'     => [
										'type'   => 'part',
										'path'   => 'listing-category/view/listing-category-description',
										'_order' => 30,
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
