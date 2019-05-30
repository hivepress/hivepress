<?php
/**
 * Vendor view block template.
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
			'tag'        => 'article',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-vendor', 'hp-vendor--view-block' ],
			],

			'blocks'     => [
				'header'  => [
					'type'       => 'container',
					'tag'        => 'header',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-vendor__header' ],
					],

					'blocks'     => [
						'image' => [
							'type'      => 'element',
							'filepath' => 'vendor/view/block/image',
							'order'     => 10,
						],
					],
				],

				'content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-vendor__content' ],
					],

					'blocks'     => [
						'name'            => [
							'type'      => 'element',
							'filepath' => 'vendor/view/block/name',
							'order'     => 10,
						],

						'details_primary' => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
							],

							'blocks'     => [
								'date' => [
									'type'      => 'element',
									'filepath' => 'vendor/view/date',
									'order'     => 10,
								],
							],
						],
					],
				],
			],
		],
	],
];
