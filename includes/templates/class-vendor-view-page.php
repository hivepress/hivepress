<?php
/**
 * Vendor view page template.
 *
 * @template vendor_view_page
 * @description Vendor page in view context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor view page template class.
 *
 * @class Vendor_View_Page
 */
class Vendor_View_Page extends Page_Sidebar_Left {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_columns' => [
						'attributes' => [
							'class' => [ 'hp-vendor', 'hp-vendor--view-page' ],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'vendor_summary'            => [
								'type'       => 'container',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-vendor__summary', 'hp-widget', 'widget' ],
								],

								'blocks'     => [
									'vendor_image'       => [
										'type'   => 'part',
										'path'   => 'vendor/view/page/vendor-image',
										'_order' => 10,
									],

									'vendor_name'        => [
										'type'       => 'container',
										'tag'        => 'h3',
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
										],
									],

									'vendor_details_primary' => [
										'type'       => 'container',
										'_order'     => 30,

										'attributes' => [
											'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
										],

										'blocks'     => [
											'vendor_registered_date' => [
												'type'   => 'part',
												'path'   => 'vendor/view/vendor-registered-date',
												'_order' => 10,
											],
										],
									],

									'vendor_attributes_secondary' => [
										'type'   => 'part',
										'path'   => 'vendor/view/page/vendor-attributes-secondary',
										'_order' => 40,
									],

									'vendor_description' => [
										'type'   => 'part',
										'path'   => 'vendor/view/page/vendor-description',
										'_order' => 50,
									],
								],
							],

							'vendor_attributes_primary' => [
								'type'   => 'part',
								'path'   => 'vendor/view/page/vendor-attributes-primary',
								'_order' => 20,
							],

							'vendor_actions_primary'    => [
								'type'       => 'container',
								'blocks'     => [],
								'_order'     => 30,

								'attributes' => [
									'class' => [ 'hp-vendor__actions', 'hp-vendor__actions--primary', 'hp-widget', 'widget' ],
								],
							],

							'page_sidebar_widgets'      => [
								'type'   => 'widgets',
								'area'   => 'hp_vendor_view_sidebar',
								'_order' => 40,
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
										'type'    => 'listings',
										'columns' => 2,
										'_order'  => 10,
									],

									'listing_pagination' => [
										'type'   => 'part',
										'path'   => 'page/pagination',
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
