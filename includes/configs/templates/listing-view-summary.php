<?php
/**
 * Listing view summary template.
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
				'tag'        => 'article',
				'attributes' => [
					'class' => [ 'hp-listing', 'hp-listing--view-summary' ],
				],
			],
			'blocks'     => [
				'header'  => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'tag'        => 'header',
						'attributes' => [
							'class' => [ 'hp-listing__header' ],
						],
					],
					'blocks'     => [
						'image' => [
							'type'       => 'element',
							'order'      => 10,
							'attributes' => [
								'file_path' => 'listing/view-summary/image',
							],
						],
					],
				],
				'content' => [
					'type'       => 'container',
					'order'      => 20,
					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-listing__content' ],
						],
					],
					'blocks'     => [
						'title'                => [
							'type'       => 'element',
							'order'      => 10,
							'attributes' => [
								'file_path' => 'listing/view-summary/title',
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
						'attributes_secondary' => [
							'type'       => 'element',
							'order'      => 30,
							'attributes' => [
								'file_path' => 'listing/view-summary/attributes-secondary',
							],
						],
					],
				],
				'footer'  => [
					'type'       => 'container',
					'order'      => 30,
					'attributes' => [
						'tag'        => 'footer',
						'attributes' => [
							'class' => [ 'hp-listing__footer' ],
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
										'file_path' => 'listing/view-summary/attributes-primary',
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
					],
				],
			],
		],
	],
];
