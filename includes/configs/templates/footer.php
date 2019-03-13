<?php
/**
 * Footer template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		// todo.
		'user_login_modal' => [
			'type'       => 'container',

			'attributes' => [
				'attributes' => [
					'id'             => 'user_login_modal',
					'class'          => 'hp-modal',
					'data-component' => 'modal',
				],
			],

			'blocks'     => [
				'user_login_form' => [
					'type'       => 'form',
					'order'      => 10,

					'attributes' => [
						'form_name' => 'user_login',
					],
				],
			],
		],

		'todo'             => [
			'type'       => 'element',
			'attributes' => [
				'file_path' => 'footer',
			],
		],
	],
];
