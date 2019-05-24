<?php
/**
 * Page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'page_header'  => [
			'type'      => 'element',
			'file_path' => 'header',
			'order'     => 10,
		],

		'page_content' => [
			'type'   => 'page_container',
			'order'  => 20,

			'blocks' => [],
		],

		'page_footer'  => [
			'type'      => 'element',
			'file_path' => 'footer',
			'order'     => 30,
		],
	],
];
