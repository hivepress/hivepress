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
 * Base listing page.
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
					'page_content' => [],

					'page_topbar'  => [
						'_label'     => hivepress()->translator->get_string( 'toolbar' ),

						'attributes' => [
							'class' => [ 'hp-page__topbar--separate' ],
						],

						'blocks'     => [
							'listing_manage_menu'       => [
								'type'       => 'menu',
								'menu'       => 'listing_manage',
								'_label'     => hivepress()->translator->get_string( 'menu' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-menu--tabbed' ],
								],
							],

							'listing_actions_secondary' => [
								'type'       => 'container',
								'optional'   => true,
								'blocks'     => [],
								'_label'     => hivepress()->translator->get_string( 'actions' ),
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--secondary' ],
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
