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
		'vendor_container' => [
			'type'       => 'container',
			'tag'        => 'article',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-vendor', 'hp-vendor--view-block' ],
			],

			'blocks'     => [
				'vendor_header'  => [
					'type'       => 'container',
					'tag'        => 'header',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-vendor__header' ],
					],

					'blocks'     => [
						'vendor_image' => [
							'type'     => 'element',
							'filepath' => 'vendor/view/block/image',
							'order'    => 10,
						],
					],
				],

				'vendor_content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-vendor__content' ],
					],

					'blocks'     => [
						'vendor_name'            => [
							'type'     => 'element',
							'filepath' => 'vendor/view/block/name',
							'order'    => 10,
						],

						'vendor_details_primary' => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-vendor__details', 'hp-vendor__details--primary' ],
							],

							'blocks'     => [
								'vendor_date' => [
									'type'     => 'element',
									'filepath' => 'vendor/view/date',
									'order'    => 10,
								],
							],
						],
					],
				],
			],
		],
	],
];
