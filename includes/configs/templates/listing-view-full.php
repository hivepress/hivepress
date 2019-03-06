<?php
/**
 * Listing view full template.
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
				'file_path' => 'listing/view-full/title',
			],
		],
		'category'    => [
			'type'       => 'element',
			'order'      => 15,
			'attributes' => [
				'file_path' => 'listing/category',
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
		'images'      => [
			'type'       => 'element',
			'order'      => 40,
			'attributes' => [
				'file_path' => 'listing/view-full/images',
			],
		],
	],
];
