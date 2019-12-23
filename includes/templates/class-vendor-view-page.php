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
class Vendor_View_Page extends Page {

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
					'page_container' => [
						'attributes' => [
							'class' => [ 'hp-vendor', 'hp-vendor--view-page' ],
						],

						'blocks'     => [
							'page_columns' => [
								'type'       => 'container',
								'_order'      => 10,

								'attributes' => [
									'class' => [ 'hp-row' ],
								],

								'blocks'     => [
									'page_sidebar' => [
										'type'       => 'container',
										'tag'        => 'aside',
										'_order'      => 10,

										'attributes' => [
											'class' => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
											'data-component' => 'sticky',
										],

										'blocks'     => [
											'vendor_summary' => [
												'type'   => 'container',
												'_order'  => 10,

												'attributes' => [
													'class' => [ 'hp-vendor__summary', 'hp-widget', 'widget' ],
												],

												'blocks' => [
													'vendor_image' => [
														'type'     => 'element',
														'filepath' => 'vendor/view/page/vendor-image',
														'_order'    => 10,
													],

													'vendor_name'  => [
														'type'     => 'element',
														'filepath' => 'vendor/view/page/vendor-name',
														'_order'    => 20,
													],

													'vendor_details_primary' => [
														'type'       => 'container',
														'_order'      => 30,

														'attributes' => [
															'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
														],

														'blocks'     => [
															'vendor_date' => [
																'type' => 'element',
																'filepath' => 'vendor/view/vendor-date',
																'_order' => 10,
															],
														],
													],

													'vendor_description' => [
														'type'     => 'element',
														'filepath' => 'vendor/view/page/vendor-description',
														'_order'    => 40,
													],
												],
											],

											'vendor_actions_primary' => [
												'type'   => 'container',
												'_order'  => 20,

												'attributes' => [
													'class' => [ 'hp-vendor__actions', 'hp-vendor__actions--primary', 'hp-widget', 'widget' ],
												],

												'blocks' => [],
											],

											'page_sidebar_widgets' => [
												'type'  => 'widgets',
												'area'  => 'vendor_sidebar',
												'_order' => 30,
											],
										],
									],

									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'_order'      => 20,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'page_title' => [
												'type'     => 'element',
												'filepath' => 'page/page-title',
												'_order'    => 5,
											],

											'listings'   => [
												'type'    => 'listings',
												'columns' => 2,
												'_order'   => 10,
											],

											'listing_pagination' => [
												'type'     => 'element',
												'filepath' => 'page/pagination',
												'_order'    => 20,
											],
										],
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
