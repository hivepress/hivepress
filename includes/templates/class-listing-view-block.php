<?php
/**
 * Listing view block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing block in view context.
 */
class Listing_View_Block extends Template {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'listing',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'listing_container' => [
						'type'       => 'container',
						'tag'        => 'article',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--view-block' ],
						],

						'blocks'     => [
							'listing_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-listing__header' ],
								],

								'blocks'     => [
									'listing_featured_badge' => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-featured-badge',
										'_order' => 10,
									],

									'listing_image' => [
										'type'   => 'part',
										'path'   => 'listing/view/block/listing-image',
										'_order' => 20,
									],
								],
							],

							'listing_content' => [
								'type'       => 'container',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__content' ],
								],

								'blocks'     => [
									'listing_title' => [
										'type'       => 'container',
										'tag'        => 'h4',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-listing__title' ],
										],

										'blocks'     => [
											'listing_title_text'           => [
												'type'   => 'part',
												'path'   => 'listing/view/block/listing-title',
												'_order' => 10,
											],

											'listing_verified_badge' => [
												'type'   => 'part',
												'path'   => 'listing/view/listing-verified-badge',
												'_order' => 20,
											],
										],
									],

									'listing_details_primary' => [
										'type'       => 'container',
										'optional'   => true,
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
										],

										'blocks'     => [
											'listing_category' => [
												'type'   => 'part',
												'path'   => 'listing/view/listing-categories',
												'_order' => 10,
											],

											'listing_created_date' => [
												'type'   => 'part',
												'path'   => 'listing/view/listing-created-date',
												'_order' => 20,
											],
										],
									],

									'listing_attributes_secondary' => [
										'type'    => 'attributes',
										'model'   => 'listing',
										'area'    => 'view_block_secondary',
										'columns' => 2,
										'_order'  => 30,
									],

									'listing_attributes_ternary' => [
										'type'   => 'attributes',
										'model'  => 'listing',
										'area'   => 'view_block_ternary',
										'_order' => 40,
									],
								],
							],

							'listing_footer'  => [
								'type'       => 'container',
								'tag'        => 'footer',
								'_order'     => 30,

								'attributes' => [
									'class' => [ 'hp-listing__footer' ],
								],

								'blocks'     => [
									'listing_attributes_primary' => [
										'type'   => 'attributes',
										'model'  => 'listing',
										'area'   => 'view_block_primary',
										'_order' => 10,
									],

									'listing_actions_primary'    => [
										'type'       => 'container',
										'blocks'     => [],
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
										],
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
