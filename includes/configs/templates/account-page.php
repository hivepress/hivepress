<?php
/**
 * Account page template.
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
				'page_columns' => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-row' ],
					],

					'blocks'     => [
						'page_sidebar' => [
							'type'       => 'container',
							'tag'        => 'aside',
							'order'      => 10,

							'attributes' => [
								'class'          => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
								'data-component' => 'sticky',
							],

							'blocks'     => [
								'account_menu' => [
									'type'       => 'menu',
									'menu'       => 'account',
									'order'      => 10,

									'attributes' => [
										'class' => [ 'hp-widget', 'widget', 'widget_nav_menu' ],
									],
								],
							],
						],

						'page_content' => [
							'type'       => 'container',
							'tag'        => 'main',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
							],

							'blocks'     => [
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
		],
	],
];
