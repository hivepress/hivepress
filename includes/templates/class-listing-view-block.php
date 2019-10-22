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
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected static $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'listing_container' => [
						'type'       => 'container',
						'tag'        => 'article',
						'order'      => 10,

						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--view-block' ],
						],

						'blocks'     => [
							'listing_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'order'      => 10,

								'attributes' => [
									'class' => [ 'hp-listing__header' ],
								],

								'blocks'     => [
									'listing_image' => [
										'type'     => 'element',
										'filepath' => 'listing/view/block/listing-image',
										'order'    => 10,
									],
								],
							],

							'listing_content' => [
								'type'       => 'container',
								'order'      => 20,

								'attributes' => [
									'class' => [ 'hp-listing__content' ],
								],

								'blocks'     => [
									'listing_title' => [
										'type'       => 'container',
										'tag'        => 'h4',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-listing__title' ],
										],

										'blocks'     => [
											'listing_title_text'           => [
												'type'     => 'element',
												'filepath' => 'listing/view/block/listing-title',
												'order'    => 10,
											],
										],
									],

									'listing_details_primary' => [
										'type'       => 'container',
										'order'      => 20,

										'attributes' => [
											'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
										],

										'blocks'     => [
											'listing_category' => [
												'type'     => 'element',
												'filepath' => 'listing/view/listing-category',
												'order'    => 10,
											],

											'listing_date' => [
												'type'     => 'element',
												'filepath' => 'listing/view/listing-date',
												'order'    => 20,
											],
										],
									],

									'listing_attributes_secondary' => [
										'type'     => 'element',
										'filepath' => 'listing/view/block/listing-attributes-secondary',
										'order'    => 30,
									],
								],
							],

							'listing_footer'  => [
								'type'       => 'container',
								'tag'        => 'footer',
								'order'      => 30,

								'attributes' => [
									'class' => [ 'hp-listing__footer' ],
								],

								'blocks'     => [
									'listing_attributes_primary' => [
										'type'     => 'element',
										'filepath' => 'listing/view/block/listing-attributes-primary',
										'order'    => 10,
									],

									'listing_actions_primary'    => [
										'type'       => 'container',
										'order'      => 20,

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

		parent::init( $args );
	}
}
