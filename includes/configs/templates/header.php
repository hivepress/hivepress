<?php
/**
 * Header template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'todo' => [
			'type'       => 'element',
			'attributes' => [
				'file_path' => 'header',
			],
		],
	],
];
