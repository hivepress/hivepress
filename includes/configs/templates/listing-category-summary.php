<?php
/**
 * Listing category summary template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// todo.
return [
	'blocks' => [
		'container' => [
			'type'       => 'container',
			'order'      => 10,
			'attributes' => [
				'tag'        => 'article',
				'attributes' => [
					'class' => [ 'hp-listing-category hp-listing-category--view-summary' ],
				],
			],
			'blocks'     => [
				'image'       => [
					'type'       => 'element',
					'order'      => 10,
					'attributes' => [
						'file_path' => 'category/view-summary/image',
					],
				],
				'name'        => [
					'type'       => 'element',
					'order'      => 20,
					'attributes' => [
						'file_path' => 'category/view-summary/name',
					],
				],
				'count'       => [
					'type'       => 'element',
					'order'      => 30,
					'attributes' => [
						'file_path' => 'category/count',
					],
				],
				'description' => [
					'type'       => 'element',
					'order'      => 40,
					'attributes' => [
						'file_path' => 'category/description',
					],
				],
			],
		],
	],
];
