<?php
/**
 * Listing submit page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'page',

	'blocks' => [
		'page_container' => [
			'blocks' => [
				'page_content' => [
					'type'   => 'container',
					'tag'    => 'main',
					'order'  => 10,

					'blocks' => [
						'page_title' => [
							'type'     => 'element',
							'filepath' => 'page/title',
							'order'    => 5,
						],
					],
				],
			],
		],
	],
];
