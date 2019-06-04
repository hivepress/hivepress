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
 * Listing category view block template class.
 *
 * @class Listing_Category_View_Block
 */
class Listing_Category_View_Block extends Template {

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
					'listing_category_container' => [
						'type'       => 'container',
						'tag'        => 'article',
						'order'      => 10,

						'attributes' => [
							'class' => [ 'hp-listing-category', 'hp-listing-category--view-block' ],
						],

						'blocks'     => [
							'listing_category_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'order'      => 10,

								'attributes' => [
									'class' => [ 'hp-listing-category__header' ],
								],

								'blocks'     => [
									'listing_category_image' => [
										'type'     => 'element',
										'filepath' => 'listing-category/view/block/image',
										'order'    => 10,
									],
								],
							],

							'listing_category_content' => [
								'type'       => 'container',
								'order'      => 20,

								'attributes' => [
									'class' => [ 'hp-listing-category__content' ],
								],

								'blocks'     => [
									'listing_category_name'            => [
										'type'     => 'element',
										'filepath' => 'listing-category/view/block/name',
										'order'    => 10,
									],

									'listing_category_details_primary' => [
										'type'       => 'container',
										'order'      => 20,

										'attributes' => [
											'class' => [ 'hp-listing-category__details', 'hp-listing-category__details--primary' ],
										],

										'blocks'     => [
											'listing_category_count' => [
												'type'     => 'element',
												'filepath' => 'listing-category/view/count',
												'order'    => 10,
											],
										],
									],

									'listing_category_description'     => [
										'type'     => 'element',
										'filepath' => 'listing-category/view/description',
										'order'    => 30,
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
