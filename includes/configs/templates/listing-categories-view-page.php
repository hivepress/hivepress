<?php
/**
 * Listing categories view page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'header'  => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'tag' => 'header',
			],

			'blocks'     => [
				'search_form' => [
					'type'  => 'listing_search_form',
					'order' => 10,
				],
			],
		],

		'content' => [
			'type'       => 'container',
			'order'      => 20,

			'attributes' => [
				'tag' => 'main',
			],

			'blocks'     => [
				'results' => [
					'type'       => 'listing_categories',
					'order'      => 10,

					'attributes' => [
						'columns' => 3,
					],
				],
			],
		],
	],
];
