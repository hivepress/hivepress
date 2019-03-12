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
	'blocks' => [
		'container' => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'attributes' => [
					'class' => [ 'hp-listing', 'hp-listing--view-page' ],
				],
			],

			'blocks'     => [
				'columns' => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-row' ],
						],
					],

					'blocks'     => [
						'content' => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'tag'        => 'main',
								'attributes' => [
									'class' => [ 'hp-listing__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
								],
							],

							'blocks'     => [
								'title'                => [
									'type'       => 'element',
									'order'      => 10,

									'attributes' => [
										'file_path' => 'listing/view/page/title',
									],
								],

								'details_primary'      => [
									'type'       => 'container',
									'order'      => 20,

									'attributes' => [
										'attributes' => [
											'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
										],
									],

									'blocks'     => [
										'category' => [
											'type'       => 'element',
											'order'      => 10,

											'attributes' => [
												'file_path' => 'listing/view/category',
											],
										],

										'date'     => [
											'type'       => 'element',
											'order'      => 20,

											'attributes' => [
												'file_path' => 'listing/view/date',
											],
										],
									],
								],

								'images'               => [
									'type'       => 'element',
									'order'      => 30,

									'attributes' => [
										'file_path' => 'listing/view/page/images',
									],
								],

								'attributes_secondary' => [
									'type'       => 'element',
									'order'      => 40,

									'attributes' => [
										'file_path' => 'listing/view/page/attributes-secondary',
									],
								],

								'description'          => [
									'type'       => 'element',
									'order'      => 50,

									'attributes' => [
										'file_path' => 'listing/view/description',
									],
								],
							],
						],

						'sidebar' => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'tag'        => 'aside',
								'attributes' => [
									'class'          => [ 'hp-listing__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
									'data-component' => 'sticky',
								],
							],

							'blocks'     => [
								'attributes_primary' => [
									'type'       => 'element',
									'order'      => 10,

									'attributes' => [
										'file_path' => 'listing/view/page/attributes-primary',
									],
								],

								'actions_primary'    => [
									'type'       => 'container',
									'order'      => 20,

									'attributes' => [
										'attributes' => [
											'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
										],
									],
								],

								'vendor'             => [
									'type'       => 'vendor',
									'order'      => 30,

									'attributes' => [
										'template_name' => 'vendor_view_block',
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
