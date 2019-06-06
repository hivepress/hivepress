<?php
/**
 * Listing edit block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing edit block template class.
 *
 * @class Listing_Edit_Block
 */
class Listing_Edit_Block extends Template {

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
						'tag'        => 'tr',
						'order'      => 10,

						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--edit-block' ],
						],

						'blocks'     => [
							'listing_title'           => [
								'type'     => 'element',
								'filepath' => 'listing/edit/block/listing-title',
								'order'    => 10,
							],

							'listing_status'          => [
								'type'     => 'element',
								'filepath' => 'listing/edit/block/listing-status',
								'order'    => 20,
							],

							'listing_date'            => [
								'type'     => 'element',
								'filepath' => 'listing/edit/block/listing-date',
								'order'    => 30,
							],

							'listing_actions_primary' => [
								'type'       => 'container',
								'tag'        => 'td',
								'order'      => 40,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
								],

								'blocks'     => [
									'listing_view_link' => [
										'type'     => 'element',
										'filepath' => 'listing/edit/block/listing-view-link',
										'order'    => 10,
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
