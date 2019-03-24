<?php
/**
 * Listing edit page template.
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
				'update_form' => [
					'type'      => 'form',
					'form_name' => 'listing_update',
					'order'     => 10,
				],
			],
		],
	],
];
