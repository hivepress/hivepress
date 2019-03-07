<?php
/**
 * Image sizes configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'landscape_small' => [
		'width'  => 400,
		'height' => 300,
		'crop'   => true,
	],

	'landscape_large' => [
		'width'  => 800,
		'height' => 600,
		'crop'   => true,
	],
];
