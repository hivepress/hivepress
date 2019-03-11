<?php
/**
 * Listing category block view template.
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
					'class' => [ 'hp-listing-category', 'hp-listing-category--block-view' ],
				],
			],

			'blocks'     => [
				'header'  => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'tag'        => 'header',
						'attributes' => [
							'class' => [ 'hp-listing-category__header' ],
						],
					],

					'blocks'     => [
						'image' => [
							'type'       => 'element',
							'order'      => 10,

							'attributes' => [
								'file_path' => 'listing-category/block/image',
							],
						],
					],
				],

				'content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-listing-category__content' ],
						],
					],

					'blocks'     => [
						'name'        => [
							'type'       => 'element',
							'order'      => 10,

							'attributes' => [
								'file_path' => 'listing-category/block/name',
							],
						],

						'summary'     => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing-category__summary' ],
								],
							],

							'blocks'     => [
								'count' => [
									'type'       => 'element',
									'order'      => 10,

									'attributes' => [
										'file_path' => 'listing-category/count',
									],
								],
							],
						],

						'description' => [
							'type'       => 'element',
							'order'      => 30,

							'attributes' => [
								'file_path' => 'listing-category/description',
							],
						],
					],
				],
			],
		],
	],
];
