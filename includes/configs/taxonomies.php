<?php
/**
 * Taxonomies configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listing_category' => [
		'post_type'         => [ 'listing', 'listing_attribute' ],
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => [ 'slug' => 'listing-category' ],

		'labels'            => [
			'name'          => esc_html__( 'Categories', 'hivepress' ),
			'singular_name' => esc_html__( 'Category', 'hivepress' ),
			'add_new_item'  => esc_html__( 'Add Category', 'hivepress' ),
			'edit_item'     => esc_html__( 'Edit Category', 'hivepress' ),
			'update_item'   => esc_html__( 'Update Category', 'hivepress' ),
			'view_item'     => esc_html__( 'View Category', 'hivepress' ),
			'parent_item'   => esc_html__( 'Parent Category', 'hivepress' ),
			'search_items'  => esc_html__( 'Search Categories', 'hivepress' ),
			'not_found'     => esc_html__( 'No categories found.', 'hivepress' ),
		],
	],

	'vendor_category'  => [
		'post_type'         => [ 'vendor', 'vendor_attribute' ],
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => [ 'slug' => 'vendor-category' ],

		'labels'            => [
			'name'          => esc_html__( 'Categories', 'hivepress' ),
			'singular_name' => esc_html__( 'Category', 'hivepress' ),
			'add_new_item'  => esc_html__( 'Add Category', 'hivepress' ),
			'edit_item'     => esc_html__( 'Edit Category', 'hivepress' ),
			'update_item'   => esc_html__( 'Update Category', 'hivepress' ),
			'view_item'     => esc_html__( 'View Category', 'hivepress' ),
			'parent_item'   => esc_html__( 'Parent Category', 'hivepress' ),
			'search_items'  => esc_html__( 'Search Categories', 'hivepress' ),
			'not_found'     => esc_html__( 'No categories found.', 'hivepress' ),
		],
	],
];
