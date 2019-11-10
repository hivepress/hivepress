<?php
/**
 * Strings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'add_listing'    => esc_html__( 'Add Listing', 'hivepress' ),
	'report_listing' => esc_html__( 'Report Listing', 'hivepress' ),
	'delete_listing' => esc_html__( 'Delete Listing', 'hivepress' ),
];
