<?php
/**
 * Tools configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'status'     => [
		'title'    => hivepress()->translator->get_string( 'status' ),
		'_order'   => 10,
	],
];
