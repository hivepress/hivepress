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
	'square_small'    => [
		'label'  => esc_html__( 'Square size', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'small', 'size', 'hivepress' ) ),
		'width'  => 400,
		'height' => 400,
		'crop'   => true,
	],

	'landscape_small' => [
		'label'  => esc_html__( 'Landscape size', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'small', 'size', 'hivepress' ) ),
		'width'  => 400,
		'height' => 300,
		'crop'   => true,
	],

	'landscape_large' => [
		'label'  => esc_html__( 'Landscape size', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'large', 'size', 'hivepress' ) ),
		'width'  => 800,
		'height' => 600,
		'crop'   => true,
	],
];
