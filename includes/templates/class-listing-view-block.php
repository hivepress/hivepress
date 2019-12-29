<?php
/**
 * Listing view block template.
 *
 * @template listing_view_block
 * @description Listing block in view context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing view block template class.
 *
 * @class Listing_View_Block
 */
class Listing_View_Block extends Template {

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
										'type'     => 'element',
										'filepath' => 'listing/view/listing-featured-badge',
										'_order'   => 10,
									],

									'listing_image' => [
										'type'     => 'element',
										'filepath' => 'listing/view/block/listing-image',
										'_order'   => 20,
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
												'type'     => 'element',
												'filepath' => 'listing/view/block/listing-title',
												'_order'   => 10,
											],

											'listing_verified_badge' => [
												'type'     => 'element',
												'filepath' => 'listing/view/listing-verified-badge',
												'_order'   => 20,
											],
										],
									],

									'listing_details_primary' => [
										'type'       => 'container',
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
										],

										'blocks'     => [
											'listing_category' => [
												'type'     => 'element',
												'filepath' => 'listing/view/listing-category',
												'_order'   => 10,
											],

											'listing_date' => [
												'type'     => 'element',
												'filepath' => 'listing/view/listing-date',
												'_order'   => 20,
											],
										],
									],

									'listing_attributes_secondary' => [
										'type'     => 'element',
										'filepath' => 'listing/view/block/listing-attributes-secondary',
										'_order'   => 30,
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
										'type'     => 'element',
										'filepath' => 'listing/view/block/listing-attributes-primary',
										'_order'   => 10,
									],

									'listing_actions_primary'    => [
										'type'       => 'container',
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
										],

										'blocks'     => [],
									],
								],
							],
						],
					],
				],
			],
			$args,
			'blocks'
		);

		parent::__construct( $args );
	}
}
