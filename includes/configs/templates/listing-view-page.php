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
		'page_content' => [
			'attributes' => [
				'class' => [ 'hp-listing', 'hp-listing--view-page' ],
			],

			'blocks'     => [
				'columns' => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-row' ],
					],

					'blocks'     => [
						'content' => [
							'type'       => 'container',
							'tag'        => 'main',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-listing__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
							],

							'blocks'     => [
								'title'                => [
									'type'      => 'element',
									'filepath' => 'listing/view/page/title',
									'order'     => 10,
								],

								'details_primary'      => [
									'type'       => 'container',
									'order'      => 20,

									'attributes' => [
										'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
									],

									'blocks'     => [
										'category' => [
											'type'      => 'element',
											'filepath' => 'listing/view/category',
											'order'     => 10,
										],

										'date'     => [
											'type'      => 'element',
											'filepath' => 'listing/view/date',
											'order'     => 20,
										],
									],
								],

								'images'               => [
									'type'      => 'element',
									'filepath' => 'listing/view/page/images',
									'order'     => 30,
								],

								'attributes_secondary' => [
									'type'      => 'element',
									'filepath' => 'listing/view/page/attributes-secondary',
									'order'     => 40,
								],

								'description'          => [
									'type'      => 'element',
									'filepath' => 'listing/view/description',
									'order'     => 50,
								],
							],
						],

						'sidebar' => [
							'type'       => 'container',
							'tag'        => 'aside',
							'order'      => 20,

							'attributes' => [
								'class'          => [ 'hp-listing__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
								'data-component' => 'sticky',
							],

							'blocks'     => [
								'attributes_primary' => [
									'type'      => 'element',
									'filepath' => 'listing/view/page/attributes-primary',
									'order'     => 10,
								],

								'actions_primary'    => [
									'type'       => 'container',
									'order'      => 20,

									'attributes' => [
										'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary', 'widget' ],
									],

									'blocks'     => [
										'listing_report_modal' => [
											'type'        => 'modal',
											'caption' => esc_html__( 'Report Listing', 'hivepress' ),

											'blocks'      => [
												'report_form' => [
													'type' => 'form',
													'form' => 'listing_report',
													'order' => 10,

													'attributes' => [
														'class' => [ 'hp-form--narrow' ],
													],
												],
											],
										],

										'report_link' => [
											'type'      => 'element',
											'filepath' => 'listing/view/page/report-link',
											'order'     => 20,
										],
									],
								],

								'vendor'             => [
									'type'          => 'vendor',
									'template' => 'vendor_view_block',
									'order'         => 30,

									'attributes'    => [
										'class' => [ 'widget' ],
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
