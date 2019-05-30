<?php
/**
 * Footer block template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'user_login_modal'            => [
			'type'    => 'modal',
			'caption' => esc_html__( 'Sign In', 'hivepress' ),

			'blocks'  => [
				'user_login_form' => [
					'type'  => 'user_login_form',
					'order' => 10,
				],
			],
		],

		'user_register_modal'         => [
			'type'    => 'modal',
			'caption' => esc_html__( 'Register', 'hivepress' ),

			'blocks'  => [
				'user_register_form' => [
					'type'       => 'form',
					'form'       => 'user_register',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-form--narrow' ],
					],

					'footer'     => [
						'form_actions' => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form__actions' ],
							],

							'blocks'     => [
								'user_login_link' => [
									'type'     => 'element',
									'filepath' => 'user/login-link',
									'order'    => 10,
								],
							],
						],
					],
				],
			],
		],

		'user_password_request_modal' => [
			'type'    => 'modal',
			'caption' => esc_html__( 'Reset Password', 'hivepress' ),

			'blocks'  => [
				'user_password_request_form' => [
					'type'        => 'form',
					'form'        => 'user_password_request',
					'description' => esc_html__( 'Please enter your username or email address, you will receive a link to create a new password via email.', 'hivepress' ),
					'order'       => 10,

					'attributes'  => [
						'class' => [ 'hp-form--narrow' ],
					],
				],
			],
		],
	],
];
