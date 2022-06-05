<?php
/**
 * Vendors view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendors page in view context.
 */
class Vendors_View_Page extends Page_Sidebar_Left {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'vendors' ),
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
				'blocks' => [
					'page_header'  => [
						'blocks' => [
							'vendor_search_form' => [
								'type'   => 'vendor_search_form',
								'_order' => 10,
							],

							'vendor_filter_link' => [
								'type'    => 'part',
								'path'    => 'vendor/view/vendor-filter-link',
								'_parent' => 'vendor_filter_container',
								'_order'  => 20,
							],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'vendor_filter_container' => [
								'type'       => 'container',
								'_label'     => hivepress()->translator->get_string( 'filter_form' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'widget', 'hp-widget', 'hp-widget--vendor-filter' ],
								],

								'blocks'     => [
									'vendor_filter_modal' => [
										'type'       => 'modal',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-modal--mobile' ],
										],

										'blocks'     => [
											'vendor_filter_form' => [
												'type'   => 'form',
												'form'   => 'vendor_filter',
												'_order' => 10,
											],
										],
									],
								],
							],

							'page_sidebar_widgets'    => [
								'type'   => 'widgets',
								'area'   => 'hp_vendors_view_sidebar',
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
							'vendor_count'     => [
								'type'   => 'result_count',
								'_label' => hivepress()->translator->get_string( 'result_count' ),
								'_order' => 10,
							],

							'vendor_sort_form' => [
								'type'       => 'form',
								'form'       => 'vendor_sort',
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
							'vendors_container' => [
								'type'   => 'results',
								'_order' => 20,

								'blocks' => [
									'vendors'           => [
										'type'      => 'vendors',
										'columns'   => 2,
										'_label'    => true,
										'_settings' => [ 'columns' ],
										'_order'    => 10,
									],

									'vendor_pagination' => [
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
