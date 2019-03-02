<?php
/**
 * Templates configuration.
 *
 * @package HivePress\Configs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing'  => [
		'blocks' => [
			'content' => [
				'type'       => 'container',
				'order'      => 20,
				'attributes' => [
					'tag'        => 'div',
					'attributes' => [
						'class' => 'hp-row',
					],
				],
				'blocks'     => [
					'content' => [
						'type'       => 'container',
						'order'      => 20,
						'attributes' => [
							'tag'        => 'main',
							'attributes' => [
								'class' => 'hp-col-sm-8 hp-col-xs-12',
							],
						],
						'blocks'     => [],
					],
					'sidebar' => [
						'type'       => 'container',
						'order'      => 10,
						'attributes' => [
							'tag'        => 'aside',
							'attributes' => [
								'class' => 'hp-col-sm-4 hp-col-xs-12',
							],
						],
						'blocks'     => [],
					],
				],
			],
		],
	],
	'listings' => [
		'blocks' => [
			'header'  => [
				'type'       => 'container',
				'order'      => 10,
				'attributes' => [
					'tag' => 'header',
				],
				'blocks'     => [
					'listing_search_form' => [
						'type'  => 'listing_search_form',
						'order' => 10,
					],
				],
			],
			'content' => [
				'type'       => 'container',
				'order'      => 20,
				'attributes' => [
					'tag'        => 'div',
					'attributes' => [
						'class' => 'hp-row',
					],
				],
				'blocks'     => [
					'sidebar' => [
						'type'       => 'container',
						'order'      => 10,
						'attributes' => [
							'tag'        => 'aside',
							'attributes' => [
								'class' => 'hp-col-sm-4 hp-col-xs-12',
							],
						],
						'blocks'     => [],
					],
					'content' => [
						'type'       => 'container',
						'order'      => 20,
						'attributes' => [
							'tag'        => 'main',
							'attributes' => [
								'class' => 'hp-col-sm-8 hp-col-xs-12',
							],
						],
						'blocks'     => [
							'listings' => [
								'type'  => 'listings',
								'order' => 20,
							],
						],
					],
				],
			],
		],
	],
];
