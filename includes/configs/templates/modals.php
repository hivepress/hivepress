<?php
/**
 * Modals template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		// todo.
		'user_login_modal'            => [
			'type'   => 'modal',
			'title'  => esc_html__( 'Sign In', 'hivepress' ),

			'blocks' => [
				'user_login_form' => [
					'type'       => 'form',
					'form_name'  => 'user_login',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-form--narrow' ],
					],
				],
			],
		],

		'user_register_modal'         => [
			'type'   => 'modal',
			'title'  => esc_html__( 'Register', 'hivepress' ),

			'blocks' => [
				'user_register_form' => [
					'type'       => 'form',
					'form_name'  => 'user_register',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-form--narrow' ],
					],
				],
			],
		],

		'user_password_request_modal' => [
			'type'   => 'modal',
			'title'  => esc_html__( 'Reset Password', 'hivepress' ),

			'blocks' => [
				'user_password_request_form' => [
					'type'       => 'form',
					'form_name'  => 'user_password_request',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-form--narrow' ],
					],
				],
			],
		],
	],
];
