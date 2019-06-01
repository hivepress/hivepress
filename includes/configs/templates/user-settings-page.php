<?php
/**
 * User settings page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'account_page',

	'blocks' => [
		'page_content' => [
			'blocks' => [
				'user_delete_modal' => [
					'type'    => 'modal',
					'caption' => esc_html__( 'Delete Account', 'hivepress' ),
					'order'   => 5,

					'blocks'  => [
						'user_delete_form' => [
							'type'       => 'form',
							'form'       => 'user_delete',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form--narrow' ],
							],
						],
					],
				],

				'user_update_form'  => [
					'type'   => 'form',
					'form'   => 'user_update',
					'order'  => 10,

					'footer' => [
						'form_actions' => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form__actions' ],
							],

							'blocks'     => [
								'user_delete_link' => [
									'type'     => 'element',
									'filepath' => 'user/edit/delete-link',
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
