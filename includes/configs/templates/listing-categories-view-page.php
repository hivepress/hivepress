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
			'type'   => 'container',
			'tag'    => 'header',
			'order'  => 10,

			'blocks' => [
				'search_form' => [
					'type'  => 'listing_search_form',
					'order' => 10,
				],
			],
		],

		'content' => [
			'type'   => 'container',
			'tag'    => 'main',
			'order'  => 20,

			'blocks' => [
				'results' => [
					'type'    => 'listing_categories',
					'columns' => 3,
					'order'   => 10,
				],
			],
		],
	],
];
