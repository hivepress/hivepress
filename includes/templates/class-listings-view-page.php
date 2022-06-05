<?php
/**
 * Listings view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings page in view context.
 */
class Listings_View_Page extends Page_Sidebar_Left {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'listings' ),
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
					'page_header'  => [
						'blocks' => [
							'listing_search_form' => [
								'type'   => 'listing_search_form',
								'_order' => 10,
							],

							'listing_filter_link' => [
								'type'    => 'part',
								'path'    => 'listing/view/listing-filter-link',
								'_parent' => 'listing_filter_container',
								'_order'  => 20,
							],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'listing_filter_container' => [
								'type'       => 'container',
								'_label'     => hivepress()->translator->get_string( 'filter_form' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'widget', 'hp-widget', 'hp-widget--listing-filter' ],
								],

								'blocks'     => [
									'listing_filter_modal' => [
										'type'       => 'modal',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-modal--mobile' ],
										],

										'blocks'     => [
											'listing_filter_form' => [
												'type'   => 'form',
												'form'   => 'listing_filter',
												'_order' => 10,
											],
										],
									],
								],
							],

							'page_sidebar_widgets'     => [
								'type'   => 'widgets',
								'area'   => 'hp_listings_view_sidebar',
								'_label' => hivepress()->translator->get_string( 'widgets' ),
								'_order' => 100,
							],
						],
					],

					'page_topbar'  => [
						'type'     => 'results',
						'optional' => true,
						'_label'   => hivepress()->translator->get_string( 'toolbar' ),

						'blocks'   => [
							'listing_count'     => [
								'type'   => 'result_count',
								'_label' => hivepress()->translator->get_string( 'result_count' ),
								'_order' => 10,
							],

							'listing_sort_form' => [
								'type'       => 'form',
								'form'       => 'listing_sort',
								'_label'     => hivepress()->translator->get_string( 'sort_form' ),
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-form--pivot' ],
								],
							],
						],
					],

					'page_content' => [
						'blocks' => [
							'listings_container' => [
								'type'   => 'results',
								'_order' => 20,

								'blocks' => [
									'listings'           => [
										'type'      => 'listings',
										'columns'   => 2,
										'_label'    => true,
										'_settings' => [ 'columns' ],
										'_order'    => 10,
									],

									'listing_pagination' => [
										'type'   => 'part',
										'path'   => 'page/pagination',
										'_label' => hivepress()->translator->get_string( 'pagination' ),
										'_order' => 20,
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
