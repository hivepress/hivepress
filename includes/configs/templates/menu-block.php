<?php
/**
 * Menu block template.
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
				'class' => [ 'hp-menu' ],
			],

			'blocks'     => [
				'todo'  => [
					'type'      => 'element',
					'file_path' => 'user/account-link',
					'order'     => 10,
				],

				'todo2' => [
					'type'      => 'element',
					'file_path' => 'listing/submit-button',
					'order'     => 20,
				],
			],
		],
	],
];
