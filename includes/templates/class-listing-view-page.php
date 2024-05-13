<?php
/**
 * Listing view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing page in view context.
 */
class Listing_View_Page extends Page_Sidebar_Right {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'listing' ),
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
				'attributes' => [
					'class' => [ 'hp-listing', 'hp-listing--view-page' ],
				],

				'blocks'     => [
					'page_columns' => [
						'attributes' => [
							'class' => [ 'hp-listing', 'hp-listing--view-page' ],
						],
					],

					'page_topbar'  => [
						'_label'     => hivepress()->translator->get_string( 'toolbar' ),
						'_order'     => 30,

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
								'_label'     => hivepress()->translator->get_string( 'actions' ) . ' (' . hivepress()->translator->get_string( 'secondary_plural' ) . ')',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--secondary' ],
								],
							],
						],
					],

					'page_content' => [
						'blocks' => [
							'listing_title'                => [
								'type'       => 'container',
								'tag'        => 'h1',
								'_label'     => hivepress()->translator->get_string( 'title' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-listing__title' ],
								],

								'blocks'     => [
									'listing_title_text' => [
										'type'   => 'part',
										'path'   => 'listing/view/page/listing-title',
										'_order' => 10,
									],

									'listing_verified_badge' => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-verified-badge',
										'_order' => 20,
									],
								],
							],

							'listing_details_primary'      => [
								'type'       => 'container',
								'optional'   => true,
								'_label'     => hivepress()->translator->get_string( 'details' ),
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
								],

								'blocks'     => [
									'listing_category'     => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-categories',
										'_label' => hivepress()->translator->get_string( 'category' ),
										'_order' => 10,
									],

									'listing_created_date' => [
										'type'   => 'part',
										'path'   => 'listing/view/listing-created-date',
										'_label' => hivepress()->translator->get_string( 'date' ),
										'_order' => 20,
									],
								],
							],

							'listing_images'               => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-images',
								'_label' => hivepress()->translator->get_string( 'images' ),
								'_order' => 40,
							],

							'listing_attributes_secondary' => [
								'type'      => 'attributes',
								'model'     => 'listing',
								'area'      => 'view_page_secondary',
								'columns'   => 2,
								'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'secondary_plural' ) . ')',
								'_settings' => [ 'columns' ],
								'_order'    => 50,
							],

							'listing_attributes_ternary'   => [
								'type'      => 'attributes',
								'model'     => 'listing',
								'area'      => 'view_page_ternary',
								'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'ternary_plural' ) . ')',
								'_settings' => [ 'columns' ],
								'_order'    => 60,
							],

							'listing_description'          => [
								'type'   => 'part',
								'path'   => 'listing/view/page/listing-description',
								'_label' => hivepress()->translator->get_string( 'description' ),
								'_order' => 70,
							],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'listing_attributes_primary' => [
								'type'      => 'attributes',
								'model'     => 'listing',
								'area'      => 'view_page_primary',
								'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'primary_plural' ) . ')',
								'_settings' => [ 'columns' ],
								'_order'    => 10,
							],

							'listing_actions_primary'    => [
								'type'       => 'container',
								'_label'     => hivepress()->translator->get_string( 'actions' ) . ' (' . hivepress()->translator->get_string( 'primary_plural' ) . ')',
								'_order'     => 20,

								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary', 'hp-widget', 'widget' ],
								],

								'blocks'     => [
									'listing_report_modal' => [
										'type'        => 'modal',
										'title'       => hivepress()->translator->get_string( 'report_listing' ),
										'_capability' => 'read',

										'blocks'      => [
											'listing_report_form' => [
												'type'   => 'form',
												'form'   => 'listing_report',
												'_order' => 10,
											],
										],
									],

									'listing_report_link'  => [
										'type'   => 'part',
										'path'   => 'listing/view/page/listing-report-link',
										'_order' => 1000,
									],
								],
							],

							'listing_vendor'             => [
								'type'     => 'template',
								'template' => 'vendor_view_block',
								'_label'   => hivepress()->translator->get_string( 'vendor' ),
								'_order'   => 30,
							],

							'page_sidebar_widgets'       => [
								'type'   => 'widgets',
								'area'   => 'hp_listing_view_sidebar',
								'_label' => hivepress()->translator->get_string( 'widgets' ),
								'_order' => 100,
							],
						],
					],

					'page_footer'  => [
						'blocks' => [
							'related_listings_container' => [
								'type'   => 'section',
								'title'  => hivepress()->translator->get_string( 'related_listings' ),
								'_order' => 10,

								'blocks' => [
									'related_listings' => [
										'type'      => 'related_listings',
										'columns'   => 3,
										'_label'    => hivepress()->translator->get_string( 'listings' ) . ' (' . hivepress()->translator->get_string( 'related_plural' ) . ')',
										'_settings' => [ 'columns', 'number', 'order' ],
										'_order'    => 10,
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
