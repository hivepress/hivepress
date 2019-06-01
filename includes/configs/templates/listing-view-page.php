<?php
/**
 * Listing view page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'page',

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
									'filepath' => 'listing/view/page/title',
									'order'    => 10,
								],

								'listing_details_primary' => [
									'type'       => 'container',
									'order'      => 20,

									'attributes' => [
										'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
									],

									'blocks'     => [
										'listing_category' => [
											'type'     => 'element',
											'filepath' => 'listing/view/category',
											'order'    => 10,
										],

										'listing_date'     => [
											'type'     => 'element',
											'filepath' => 'listing/view/date',
											'order'    => 20,
										],
									],
								],

								'listing_images'          => [
									'type'     => 'element',
									'filepath' => 'listing/view/page/images',
									'order'    => 30,
								],

								'listing_attributes_secondary' => [
									'type'     => 'element',
									'filepath' => 'listing/view/page/attributes-secondary',
									'order'    => 40,
								],

								'listing_description'     => [
									'type'     => 'element',
									'filepath' => 'listing/view/page/description',
									'order'    => 50,
								],
							],
						],

						'page_sidebar' => [
							'type'       => 'container',
							'tag'        => 'aside',
							'order'      => 20,

							'attributes' => [
								'class'          => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
								'data-component' => 'sticky',
							],

							'blocks'     => [
								'listing_attributes_primary' => [
									'type'     => 'element',
									'filepath' => 'listing/view/page/attributes-primary',
									'order'    => 10,
								],

								'listing_actions_primary' => [
									'type'       => 'container',
									'order'      => 20,

									'attributes' => [
										'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary', 'hp-widget', 'widget' ],
									],

									'blocks'     => [
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
											'filepath' => 'listing/view/page/report-link',
											'order'    => 20,
										],
									],
								],

								'vendor'                  => [
									'type'       => 'vendor',
									'template'   => 'vendor_view_block',
									'order'      => 30,

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
];
