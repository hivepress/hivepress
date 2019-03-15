<?php
/**
 * Actions block template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		// todo.
		'container' => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'class' => 'hp-actions',
			],

			'blocks'     => [
				'todo' => [
					'type'      => 'element',
					'file_path' => 'user/login-link',
					'order'     => 10,
				],
			],
		],
	],
];
