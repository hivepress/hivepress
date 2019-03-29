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
	'blocks' => [
		'columns' => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-row' ],
			],

			'blocks'     => [
				'sidebar' => [
					'type'       => 'container',
					'tag'        => 'aside',
					'order'      => 10,

					'attributes' => [
						'class'          => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
						'data-component' => 'sticky',
					],

					'blocks'     => [
						'menu' => [
							'type'       => 'menu',
							'menu_name'  => 'account',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'widget', 'widget_nav_menu' ],
							],
						],
					],
				],

				'content' => [
					'type'       => 'container',
					'tag'        => 'main',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
					],

					'blocks'     => [
						'title' => [
							'type'      => 'element',
							'file_path' => 'page/title',
							'order'     => 5,
						],
					],
				],
			],
		],
	],
];
