<?php
/**
 * Listing view page template.
 *
 * @template listing_view_page
 * @description Listing page in view context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing view page template class.
 *
 * @class Listing_View_Page
 */
class Listing_View_Page extends Page {

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
							'class' => [ 'hp-listing', 'hp-listing--view-page' ],
						],

						'blocks'     => [
							'page_columns' => [
								'type'       => 'container',
								'_order'      => 10,

								'attributes' => [
									'class' => [ 'hp-row' ],
								],

								'blocks'     => [
									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'_order'      => 10,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'listing_title' => [
												'type'   => 'container',
												'tag'    => 'h1',
												'_order'  => 10,

												'attributes' => [
													'class' => [ 'hp-listing__title' ],
												],

												'blocks' => [
													'listing_title_text'           => [
														'type'     => 'element',
														'filepath' => 'listing/view/page/listing-title',
														'_order'    => 10,
													],

													'listing_verified_badge' => [
														'type'     => 'element',
														'filepath' => 'listing/view/listing-verified-badge',
														'_order'    => 20,
													],
												],
											],

											'listing_details_primary' => [
												'type'   => 'container',
												'_order'  => 20,

												'attributes' => [
													'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
												],

												'blocks' => [
													'listing_category' => [
														'type'     => 'element',
														'filepath' => 'listing/view/listing-category',
														'_order'    => 10,
													],

													'listing_date'     => [
														'type'     => 'element',
														'filepath' => 'listing/view/listing-date',
														'_order'    => 20,
													],
												],
											],

											'listing_images'          => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-images',
												'_order'    => 30,
											],

											'listing_attributes_secondary' => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-attributes-secondary',
												'_order'    => 40,
											],

											'listing_description'     => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-description',
												'_order'    => 50,
											],
										],
									],

									'page_sidebar' => [
										'type'       => 'container',
										'tag'        => 'aside',
										'_order'      => 20,

										'attributes' => [
											'class' => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
											'data-component' => 'sticky',
										],

										'blocks'     => [
											'listing_attributes_primary' => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-attributes-primary',
												'_order'    => 10,
											],

											'listing_actions_primary' => [
												'type'   => 'container',
												'_order'  => 20,

												'attributes' => [
													'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary', 'hp-widget', 'widget' ],
												],

												'blocks' => [
													'listing_report_modal' => [
														'type'    => 'modal',
														'caption' => hivepress()->translator->get_string( 'report_listing' ),

														'blocks'  => [
															'listing_report_form' => [
																'type' => 'form',
																'form' => 'listing_report',
																'_order' => 10,

																'attributes' => [
																	'class' => [ 'hp-form--narrow' ],
																],
															],
														],
													],

													'listing_report_link' => [
														'type'     => 'element',
														'filepath' => 'listing/view/page/listing-report-link',
														'_order'    => 100,
													],
												],
											],

											'vendor' => [
												'type'  => 'vendor',
												'_order' => 30,

												'attributes' => [
													'class' => [ 'hp-widget', 'widget' ],
												],
											],

											'page_sidebar_widgets' => [
												'type'  => 'widgets',
												'area'  => 'listing_sidebar',
												'_order' => 40,
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
