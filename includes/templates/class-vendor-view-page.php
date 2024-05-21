<?php
/**
 * Vendor view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor page in view context.
 */
class Vendor_View_Page extends Page_Sidebar_Left {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'vendor' ),
				'model' => 'vendor',
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
					'class' => [ 'hp-vendor', 'hp-vendor--view-page' ],
				],

				'blocks'     => [
					'page_sidebar' => [
						'attributes' => [
							'class'          => [ 'hp-vendor', 'hp-vendor--view-page' ],
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'vendor_summary'            => [
								'type'       => 'container',
								'_label'     => esc_html__( 'Summary', 'hivepress' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-vendor__summary', 'hp-widget', 'widget' ],
								],

								'blocks'     => [
									'vendor_image'       => [
										'type'   => 'part',
										'path'   => 'vendor/view/page/vendor-image',
										'_label' => hivepress()->translator->get_string( 'image' ),
										'_order' => 10,
									],

									'vendor_name'        => [
										'type'       => 'container',
										'tag'        => 'h3',
										'_label'     => hivepress()->translator->get_string( 'name' ),
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-vendor__name' ],
										],

										'blocks'     => [
											'vendor_name_text'           => [
												'type'   => 'part',
												'path'   => 'vendor/view/page/vendor-name',
												'_order' => 10,
											],

											'vendor_verified_badge' => [
												'type'   => 'part',
												'path'   => 'vendor/view/vendor-verified-badge',
												'_order' => 20,
											],
										],
									],

									'vendor_details_primary' => [
										'type'       => 'container',
										'optional'   => true,
										'_label'     => hivepress()->translator->get_string( 'details' ),
										'_order'     => 30,

										'attributes' => [
											'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
										],

										'blocks'     => [
											'vendor_registered_date' => [
												'type'   => 'part',
												'path'   => 'vendor/view/vendor-registered-date',
												'_label' => hivepress()->translator->get_string( 'date' ),
												'_order' => 10,
											],
										],
									],

									'vendor_attributes_secondary' => [
										'type'      => 'attributes',
										'model'     => 'vendor',
										'area'      => 'view_page_secondary',
										'columns'   => 2,
										'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'secondary_plural' ) . ')',
										'_settings' => [ 'columns' ],
										'_order'    => 40,
									],

									'vendor_attributes_ternary' => [
										'type'      => 'attributes',
										'model'     => 'vendor',
										'area'      => 'view_page_ternary',
										'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'ternary_plural' ) . ')',
										'_settings' => [ 'columns' ],
										'_order'    => 50,
									],

									'vendor_description' => [
										'type'   => 'part',
										'path'   => 'vendor/view/page/vendor-description',
										'_label' => hivepress()->translator->get_string( 'description' ),
										'_order' => 60,
									],
								],
							],

							'vendor_attributes_primary' => [
								'type'      => 'attributes',
								'model'     => 'vendor',
								'area'      => 'view_page_primary',
								'_label'    => hivepress()->translator->get_string( 'attributes' ) . ' (' . hivepress()->translator->get_string( 'primary_plural' ) . ')',
								'_settings' => [ 'columns' ],
								'_order'    => 20,
							],

							'vendor_actions_primary'    => [
								'type'       => 'container',
								'blocks'     => [],
								'_label'     => hivepress()->translator->get_string( 'actions' ),
								'_order'     => 30,

								'attributes' => [
									'class' => [ 'hp-vendor__actions', 'hp-vendor__actions--primary', 'hp-widget', 'widget' ],
								],
							],

							'page_sidebar_widgets'      => [
								'type'   => 'widgets',
								'area'   => 'hp_vendor_view_sidebar',
								'_label' => hivepress()->translator->get_string( 'widgets' ),
								'_order' => 100,
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
