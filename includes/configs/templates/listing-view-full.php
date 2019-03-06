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
		'container' => [
			'type'       => 'container',
			'order'      => 10,
			'attributes' => [
				'tag'        => 'article',
				'attributes' => [
					'class' => [ 'hp-listing', 'hp-listing--view-full' ],
				],
			],
			'blocks'     => [
				'attributes_secondary' => [
					'type'       => 'element',
					'order'      => 10,
					'attributes' => [
						'file_path' => 'listing/view-full/attributes-secondary',
					],
				],
				'title'                => [
					'type'       => 'element',
					'order'      => 10,
					'attributes' => [
						'file_path' => 'listing/view-full/title',
					],
				],
				'summary'              => [
					'type'       => 'container',
					'order'      => 20,
					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-listing__summary' ],
						],
					],
					'blocks'     => [
						'category' => [
							'type'       => 'element',
							'order'      => 15,
							'attributes' => [
								'file_path' => 'listing/category',
							],
						],
						'date'     => [
							'type'       => 'element',
							'order'      => 20,
							'attributes' => [
								'file_path' => 'listing/date',
							],
						],
					],
				],
				'description'          => [
					'type'       => 'element',
					'order'      => 30,
					'attributes' => [
						'file_path' => 'listing/description',
					],
				],
				'images'               => [
					'type'       => 'element',
					'order'      => 40,
					'attributes' => [
						'file_path' => 'listing/view-full/images',
					],
				],
			],
		],
	],
];
