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
	'portrait_small' => [
		'width'  => 400,
		'height' => 267,
		'crop'   => true,
	],
];
