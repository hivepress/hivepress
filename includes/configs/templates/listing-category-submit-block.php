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
		'listing_category_container' => [
			'type'       => 'container',
			'tag'        => 'article',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-listing-category', 'hp-listing-category--submit-block' ],
			],

			'blocks'     => [
				'listing_category_header'  => [
					'type'       => 'container',
					'tag'        => 'header',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-listing-category__header' ],
					],

					'blocks'     => [
						'listing_category_image' => [
							'type'     => 'element',
							'filepath' => 'listing-category/submit/block/image',
							'order'    => 10,
						],
					],
				],

				'listing_category_content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-listing-category__content' ],
					],

					'blocks'     => [
						'listing_category_name' => [
							'type'     => 'element',
							'filepath' => 'listing-category/submit/block/name',
							'order'    => 10,
						],
					],
				],
			],
		],
	],
];
