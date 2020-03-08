<?php
/**
 * Listing manage page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing manage page template class.
 *
 * @class Listing_Manage_Page
 */
abstract class Listing_Manage_Page extends Page_Wide {

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
							'page_topbar' => [
								'type'       => 'container',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-page__topbar' ],
								],

								'blocks'     => [
									'listing_manage_menu' => [
										'type'   => 'menu',
										'menu'   => 'listing_manage',
										'_order' => 10,
									],

									'listing_actions_secondary' => [
										'type'       => 'container',
										'blocks'     => [],
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-listing__actions', 'hp-listing__actions--secondary' ],
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
