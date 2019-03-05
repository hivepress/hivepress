<?php
/**
 * Templates configuration.
 *
 * @package HivePress\Configs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listings_page' => [
		'blocks' => [
			'listing_search_form' => [
				'type'       => 'listing_search_form',
				'order'      => 10,
				'attributes' => [
					'attributes' => [
						'data-bind' => 'submit: renderBlock',
					],
				],
			],
			'page_container'      => [
				'type'       => 'container',
				'order'      => 20,
				'attributes' => [
					'attributes' => [
						'class' => 'hp-row',
					],
				],
				'blocks'     => [
					'page_sidebar' => [
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
					'page_content' => [
						'type'       => 'container',
						'order'      => 20,
						'attributes' => [
							'tag'        => 'main',
							'attributes' => [
								'class' => 'hp-col-sm-8 hp-col-xs-12',
							],
						],
						'blocks'     => [
							'listing_search_results' => [
								'type'  => 'listing_search_results',
								'order' => 10,
							],
						],
					],
				],
			],
		],
	],
];
