<?php
/**
 * Post types configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'email'    => [
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'hp_settings',
		'supports'     => [ 'title', 'editor' ],

		'labels'       => [
			'name'               => esc_html__( 'Emails', 'hivepress' ),
			'singular_name'      => esc_html__( 'Email', 'hivepress' ),
			'add_new'            => esc_html_x( 'Add New', 'email', 'hivepress' ),
			'add_new_item'       => esc_html__( 'Add Email', 'hivepress' ),
			'edit_item'          => esc_html__( 'Edit Email', 'hivepress' ),
			'new_item'           => esc_html__( 'Add Email', 'hivepress' ),
			'all_items'          => esc_html__( 'Emails', 'hivepress' ),
			'search_items'       => esc_html__( 'Search Emails', 'hivepress' ),
			'not_found'          => esc_html__( 'No emails found.', 'hivepress' ),
			'not_found_in_trash' => esc_html__( 'No emails found.', 'hivepress' ),
		],
	],

	'template' => [
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => true,
		'show_in_menu' => 'hp_settings',
		'supports'     => [ 'title', 'editor' ],

		'labels'       => [
			'name'               => esc_html__( 'Templates', 'hivepress' ),
			'singular_name'      => esc_html__( 'Template', 'hivepress' ),
			'add_new'            => esc_html_x( 'Add New', 'template', 'hivepress' ),
			'add_new_item'       => esc_html__( 'Add Template', 'hivepress' ),
			'edit_item'          => esc_html__( 'Edit Template', 'hivepress' ),
			'new_item'           => esc_html__( 'Add Template', 'hivepress' ),
			'all_items'          => esc_html__( 'Templates', 'hivepress' ),
			'search_items'       => esc_html__( 'Search Templates', 'hivepress' ),
			'not_found'          => esc_html__( 'No templates found.', 'hivepress' ),
			'not_found_in_trash' => esc_html__( 'No templates found.', 'hivepress' ),
		],
	],

	'listing'  => [
		'public'              => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'delete_with_user'    => true,
		'supports'            => [ 'title', 'editor', 'thumbnail' ],
		'menu_icon'           => 'dashicons-format-aside',
		'rewrite'             => [ 'slug' => 'listing' ],

		'labels'              => [
			'name'               => hivepress()->translator->get_string( 'listings' ),
			'singular_name'      => hivepress()->translator->get_string( 'listing' ),
			'add_new'            => esc_html_x( 'Add New', 'listing', 'hivepress' ),
			'add_new_item'       => hivepress()->translator->get_string( 'add_listing' ),
			'edit_item'          => hivepress()->translator->get_string( 'edit_listing' ),
			'new_item'           => hivepress()->translator->get_string( 'add_listing' ),
			'view_item'          => hivepress()->translator->get_string( 'view_listing' ),
			'all_items'          => hivepress()->translator->get_string( 'listings' ),
			'view_items'         => hivepress()->translator->get_string( 'view_listings' ),
			'search_items'       => hivepress()->translator->get_string( 'search_listings' ),
			'not_found'          => hivepress()->translator->get_string( 'no_listings_found' ),
			'not_found_in_trash' => hivepress()->translator->get_string( 'no_listings_found' ),
		],
	],

	'vendor'   => [
		'public'              => true,
		'show_ui'             => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'delete_with_user'    => true,
		'supports'            => [ 'title', 'editor', 'thumbnail' ],
		'menu_icon'           => 'dashicons-businessman',
		'rewrite'             => [ 'slug' => 'vendor' ],
		'redirect_canonical'  => false,

		'labels'              => [
			'name'               => hivepress()->translator->get_string( 'vendors' ),
			'singular_name'      => hivepress()->translator->get_string( 'vendor' ),
			'add_new'            => esc_html_x( 'Add New', 'vendor', 'hivepress' ),
			'add_new_item'       => hivepress()->translator->get_string( 'add_vendor' ),
			'edit_item'          => hivepress()->translator->get_string( 'edit_vendor' ),
			'new_item'           => hivepress()->translator->get_string( 'add_vendor' ),
			'view_item'          => hivepress()->translator->get_string( 'view_vendor' ),
			'all_items'          => hivepress()->translator->get_string( 'vendors' ),
			'search_items'       => hivepress()->translator->get_string( 'search_vendors' ),
			'not_found'          => hivepress()->translator->get_string( 'no_vendors_found' ),
			'not_found_in_trash' => hivepress()->translator->get_string( 'no_vendors_found' ),
		],
	],
];
