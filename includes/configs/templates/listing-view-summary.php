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
	'blocks' => [
		'container' => [
			'type'       => 'container',
			'order'      => 10,
			'attributes' => [
				'attributes' => [
					'class' => [ 'hp-listing hp-listing--view-summary' ],
				],
			],
			'blocks'     => [
				'header'  => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-listing__header' ],
						],
					],
					'blocks'     => [
						'image' => [
							'type'       => 'element',
							'order'      => 30,
							'attributes' => [
								'file_path' => 'listing/view-summary/image',
							],
						],
					],
				],
				'content' => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-listing__content' ],
						],
					],
					'blocks'     => [
						'title'      => [
							'type'       => 'element',
							'order'      => 10,
							'attributes' => [
								'file_path' => 'listing/view-summary/title',
							],
						],
						'summary'    => [
							'type'       => 'container',
							'order'      => 10,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing__summary' ],
								],
							],
							'blocks'     => [
								'category' => [
									'type'       => 'element',
									'order'      => 15,
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
						'attributes' => [
							'type'       => 'container',
							'order'      => 10,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing__attributes', 'hp-listing__attributes--secondary' ],
								],
							],
							'blocks'     => [
								'attributes' => [
									'type'       => 'element',
									'order'      => 20,
									'attributes' => [
										'file_path' => 'listing/view-summary/attributes-secondary',
									],
								],
							],
						],
					],
				],
				'footer'  => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-listing__footer' ],
						],
					],
					'blocks'     => [
						'attributes' => [
							'type'       => 'container',
							'order'      => 10,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing__attributes', 'hp-listing__attributes--primary' ],
								],
							],
							'blocks'     => [
								'attributes' => [
									'type'       => 'element',
									'order'      => 20,
									'attributes' => [
										'file_path' => 'listing/view-summary/attributes-primary',
									],
								],
							],
						],
						'actions'    => [
							'type'       => 'container',
							'order'      => 10,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing__actions' ],
								],
							],
						],
					],
				],
			],
		],
	],
];
