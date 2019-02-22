<?php
/**
 * Taxonomies configuration.
 *
 * @package HivePress\Configs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_category' => [
		'object_type' => 'listing',

		'args'        => [
			'hierarchical' => true,
			'rewrite'      => [ 'slug' => 'listing-category' ],
		],
	],
];
