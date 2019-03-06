<?php
/**
 * Listing todo template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'title'       => [
			'type'       => 'element',
			'order'      => 10,
			'attributes' => [
				'file_path' => 'listing/title',
			],
		],
		'date'        => [
			'type'       => 'element',
			'order'      => 20,
			'attributes' => [
				'file_path' => 'listing/date',
			],
		],
		'description' => [
			'type'       => 'element',
			'order'      => 30,
			'attributes' => [
				'file_path' => 'listing/description',
			],
		],
	],
];
