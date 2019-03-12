<?php
/**
 * Listing view block template.
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
					'class' => [ 'hp-listing', 'hp-listing--view-block' ],
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
								'file_path' => 'listing/view/block/image',
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
								'file_path' => 'listing/view/block/title',
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

						'attributes_secondary' => [
							'type'       => 'element',
							'order'      => 30,

							'attributes' => [
								'file_path' => 'listing/view/block/attributes-secondary',
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
						'attributes_primary' => [
							'type'       => 'element',
							'order'      => 10,

							'attributes' => [
								'file_path' => 'listing/view/block/attributes-primary',
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
					],
				],
			],
		],
	],
];
