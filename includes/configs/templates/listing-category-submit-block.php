<?php
/**
 * Listing category submit block template.
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
			'tag'        => 'article',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-listing-category', 'hp-listing-category--submit-block' ],
			],

			'blocks'     => [
				'header'  => [
					'type'       => 'container',
					'tag'        => 'header',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-listing-category__header' ],
					],

					'blocks'     => [
						'image' => [
							'type'      => 'element',
							'filepath' => 'listing-category/submit/block/image',
							'order'     => 10,
						],
					],
				],

				'content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-listing-category__content' ],
					],

					'blocks'     => [
						'name'            => [
							'type'      => 'element',
							'filepath' => 'listing-category/submit/block/name',
							'order'     => 10,
						],

						'details_primary' => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-listing-category__details', 'hp-listing-category__details--primary' ],
							],

							'blocks'     => [
								'count' => [
									'type'      => 'element',
									'filepath' => 'listing-category/submit/count',
									'order'     => 10,
								],
							],
						],

						'description'     => [
							'type'      => 'element',
							'filepath' => 'listing-category/submit/description',
							'order'     => 30,
						],
					],
				],
			],
		],
	],
];
