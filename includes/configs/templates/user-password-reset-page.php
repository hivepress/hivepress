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
	'blocks' => [
		'columns' => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-row' ],
			],

			'blocks'     => [
				'content' => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-col-sm-4', 'hp-col-sm-offset-4', 'hp-col-xs-12' ],
					],

					'blocks'     => [
						'password_reset_form' => [
							'type'  => 'user_password_reset_form',
							'order' => 10,
						],
					],
				],
			],
		],
	],
];
