<?php
/**
 * Vendor block view template.
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
					'class' => [ 'hp-vendor', 'hp-vendor--block-view' ],
				],
			],
			'blocks'     => [
				'header'  => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'tag'        => 'header',
						'attributes' => [
							'class' => [ 'hp-vendor__header' ],
						],
					],
					'blocks'     => [
						'image' => [
							'type'       => 'element',
							'order'      => 10,
							'attributes' => [
								'file_path' => 'vendor/block/image',
							],
						],
					],
				],
				'content' => [
					'type'       => 'container',
					'order'      => 20,
					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-vendor__content' ],
						],
					],
					'blocks'     => [
						'name'    => [
							'type'       => 'element',
							'order'      => 10,
							'attributes' => [
								'file_path' => 'vendor/block/name',
							],
						],
						'summary' => [
							'type'       => 'container',
							'order'      => 20,
							'attributes' => [
								'attributes' => [
									'class' => [ 'hp-vendor__summary' ],
								],
							],
							'blocks'     => [
								'date' => [
									'type'       => 'element',
									'order'      => 10,
									'attributes' => [
										'file_path' => 'vendor/date',
									],
								],
							],
						],
					],
				],
			],
		],
	],
];
