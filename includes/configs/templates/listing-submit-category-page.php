<?php
/**
 * Listing submit category page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'listing_submit_page',

	'blocks' => [
		'content' => [
			'blocks' => [
				'listing_categories' => [
					'type'             => 'listing_categories',
					'template' => 'submit',
					'columns'          => 3,
					'order'            => 10,
				],
			],
		],
	],
];
