<?php
/**
 * Listing view full template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'listing_page',
	'blocks' => [
		'container' => [
			'type'       => 'container',
			'order'      => 10,
			'attributes' => [
				'attributes' => [
					'class' => [ 'hp-listing', 'hp-listing--view-full', 'hp-row' ],
				],
			],
			'blocks'     => [
				'content' => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'tag'        => 'main',
						'attributes' => [
							'class' => [ 'hp-col-sm-8', 'hp-col-xs-12' ],
						],
					],
					'blocks'     => [
						'title'                => [
							'type'       => 'element',
							'order'      => 10,
							'attributes' => [
								'file_path' => 'listing/view-full/title',
							],
						],
						'summary'              => [
							'type'       => 'container',
							'order'      => 20,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing__summary' ],
								],
							],
							'blocks'     => [
								'category' => [
									'type'       => 'element',
									'order'      => 10,
									'attributes' => [
										'file_path' => 'listing/category',
									],
								],
								'date'     => [
									'type'       => 'element',
									'order'      => 20,
									'attributes' => [
										'file_path' => 'listing/date',
									],
								],
							],
						],
						'images'               => [
							'type'       => 'element',
							'order'      => 30,
							'attributes' => [
								'file_path' => 'listing/view-full/images',
							],
						],
						'attributes_secondary' => [
							'type'       => 'element',
							'order'      => 40,
							'attributes' => [
								'file_path' => 'listing/view-full/attributes-secondary',
							],
						],
						'description'          => [
							'type'       => 'element',
							'order'      => 50,
							'attributes' => [
								'file_path' => 'listing/description',
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
							'class'          => [ 'hp-col-sm-4', 'hp-col-xs-12' ],
							'data-component' => 'sticky',
						],
					],
					'blocks'     => [
						'properties'      => [
							'type'       => 'container',
							'order'      => 10,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing__properties' ],
								],
							],
							'blocks'     => [
								'attributes_primary' => [
									'type'       => 'element',
									'order'      => 10,
									'attributes' => [
										'file_path' => 'listing/view-full/attributes-primary',
									],
								],
							],
						],
						'actions_primary' => [
							'type'       => 'container',
							'order'      => 20,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
								],
							],
						],
						'vendor'          => [
							'type'       => 'vendor',
							'order'      => 30,
							'attributes' => [
								'template_name' => 'vendor_view_summary',
							],
						],
					],
				],
			],
		],
	],
];
