<?php
/**
 * Listings edit page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'account_page',

	'blocks' => [
		'content' => [
			'blocks' => [
				'listings' => [
					'type'             => 'listings',
					'template' => 'edit',
					'order'            => 10,
				],
			],
		],
	],
];
