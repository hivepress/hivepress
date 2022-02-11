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
 * Listing block in edit context.
 */
class Listing_Edit_Block extends Template {

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
						'tag'        => 'tr',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--edit-block' ],
						],

						'blocks'     => [
							'listing_title'           => [
								'type'   => 'part',
								'path'   => 'listing/edit/block/listing-title',
								'_order' => 10,
							],

							'listing_categories'      => [
								'type'   => 'part',
								'path'   => 'listing/edit/block/listing-categories',
								'_order' => 20,
							],

							'listing_created_date'    => [
								'type'   => 'part',
								'path'   => 'listing/edit/block/listing-created-date',
								'_order' => 30,
							],

							'listing_status'          => [
								'type'   => 'part',
								'path'   => 'listing/edit/block/listing-status',
								'_order' => 40,
							],

							'listing_actions_primary' => [
								'type'       => 'container',
								'tag'        => 'td',
								'_order'     => 100,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
								],

								'blocks'     => [
									'listing_view_link' => [
										'type'   => 'part',
										'path'   => 'listing/edit/block/listing-view-link',
										'_order' => 1000,
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
