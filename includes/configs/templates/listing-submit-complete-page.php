<?php
/**
 * Listing submit complete page template.
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
				'complete_message' => [
					'type'      => 'element',
					'file_path' => 'listing/submit/complete-message',
					'order'     => 10,
				],
			],
		],
	],
];
