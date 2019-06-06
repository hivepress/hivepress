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
 * Listing view page template class.
 *
 * @class Listing_View_Page
 */
class Listing_View_Page extends Page {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

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
								'order'      => 10,

								'attributes' => [
									'class' => [ 'hp-row' ],
								],

								'blocks'     => [
									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'listing_title'           => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-title',
												'order'    => 10,
											],

											'listing_details_primary' => [
												'type'   => 'container',
												'order'  => 20,

												'attributes' => [
													'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
												],

												'blocks' => [
													'listing_category' => [
														'type'     => 'element',
														'filepath' => 'listing/view/listing-category',
														'order'    => 10,
													],

													'listing_date'     => [
														'type'     => 'element',
														'filepath' => 'listing/view/listing-date',
														'order'    => 20,
													],
												],
											],

											'listing_images'          => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-images',
												'order'    => 30,
											],

											'listing_attributes_secondary' => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-attributes-secondary',
												'order'    => 40,
											],

											'listing_description'     => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-description',
												'order'    => 50,
											],
										],
									],

									'page_sidebar' => [
										'type'       => 'container',
										'tag'        => 'aside',
										'order'      => 20,

										'attributes' => [
											'class' => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
											'data-component' => 'sticky',
										],

										'blocks'     => [
											'listing_attributes_primary' => [
												'type'     => 'element',
												'filepath' => 'listing/view/page/listing-attributes-primary',
												'order'    => 10,
											],

											'listing_actions_primary' => [
												'type'   => 'container',
												'order'  => 20,

												'attributes' => [
													'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary', 'hp-widget', 'widget' ],
												],

												'blocks' => [
													'listing_report_modal' => [
														'type'    => 'modal',
														'caption' => esc_html__( 'Report Listing', 'hivepress' ),

														'blocks'  => [
															'listing_report_form' => [
																'type' => 'form',
																'form' => 'listing_report',
																'order' => 10,

																'attributes' => [
																	'class' => [ 'hp-form--narrow' ],
																],
															],
														],
													],

													'listing_report_link' => [
														'type'     => 'element',
														'filepath' => 'listing/view/page/listing-report-link',
														'order'    => 20,
													],
												],
											],

											'vendor' => [
												'type'     => 'vendor',
												'template' => 'vendor_view_block',
												'order'    => 30,

												'attributes' => [
													'class' => [ 'hp-widget', 'widget' ],
												],
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
