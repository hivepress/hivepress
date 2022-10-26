<?php
/**
 * Areas configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get areas terms.
$areas = array_reduce(
	(array) get_terms(
		[
			'taxonomy'   => 'hp_areas',
			'hide_empty' => false,
		]
	),
	function ( $result, $term ) {
		$result[ $term->slug ] = $term->name;
		return $result;
	}
);

// Add display areas.
$areas = array_merge(
	(array) $areas,
	[
		'view_block_primary'   => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
		'view_block_secondary' => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
		'view_page_primary'    => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
		'view_page_secondary'  => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
	]
);

return $areas;
