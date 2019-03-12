<?php
/**
 * Listing category view block template.
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
					'class' => [ 'hp-listing-category', 'hp-listing-category--view-block' ],
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
								'file_path' => 'listing-category/view/block/image',
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
						'name'            => [
							'type'       => 'element',
							'order'      => 10,

							'attributes' => [
								'file_path' => 'listing-category/view/block/name',
							],
						],

						'details_primary' => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-listing-category__details', 'hp-listing-category__details--primary' ],
								],
							],

							'blocks'     => [
								'count' => [
									'type'       => 'element',
									'order'      => 10,

									'attributes' => [
										'file_path' => 'listing-category/view/count',
									],
								],
							],
						],

						'description'     => [
							'type'       => 'element',
							'order'      => 30,

							'attributes' => [
								'file_path' => 'listing-category/view/description',
							],
						],
					],
				],
			],
		],
	],
];
