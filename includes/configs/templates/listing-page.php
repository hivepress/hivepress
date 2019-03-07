<?php
/**
 * Listing page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'page_container' => [
			'type'       => 'container',
			'order'      => 10,
			'attributes' => [
				'attributes' => [
					'class' => [ 'hp-row' ],
				],
			],
			'blocks'     => [
				'page_content' => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'tag'        => 'main',
						'attributes' => [
							'class' => [ 'hp-col-sm-8', 'hp-col-xs-12' ],
						],
					],
					'blocks'     => [
						'listing_view_full' => [
							'type'       => 'listing',
							'order'      => 10,
							'attributes' => [
								'template_name' => 'listing_view_full',
							],
						],
					],
				],
				'page_sidebar' => [
					'type'       => 'container',
					'order'      => 20,
					'attributes' => [
						'tag'        => 'aside',
						'attributes' => [
							'class'          => [ 'hp-col-sm-4', 'hp-col-xs-12' ],
							'data-component' => 'sticky',
						],
					],
					'blocks'     => [],
				],
			],
		],
	],
];
