<?php
/**
 * User password reset page template.
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
						'page_content' => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-page__content', 'hp-col-sm-4', 'hp-col-sm-offset-4', 'hp-col-xs-12' ],
							],

							'blocks'     => [
								'page_title'               => [
									'type'     => 'element',
									'filepath' => 'page/title',
									'order'    => 5,
								],

								'user_password_reset_form' => [
									'type'  => 'user_password_reset_form',
									'order' => 10,
								],
							],
						],
					],
				],
			],
		],
	],
];
