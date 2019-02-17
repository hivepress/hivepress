<?php
/**
 * HivePress configuration.
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [

	// Options.
	'options'     => [
		'listings'     => [
			'title'    => esc_html__( 'Listings', 'hivepress' ),
			'order'    => 10,
			'sections' => [],
		],

		'integrations' => [
			'title'    => esc_html__( 'Integrations', 'hivepress' ),
			'order'    => 100,
			'sections' => [],
		],
	],

	// Post types.
	'post_types'  => [
		'listing'           => [
			'public'      => true,
			'has_archive' => true,
			'supports'    => [ 'title', 'editor', 'thumbnail', 'author' ],
			'menu_icon'   => 'dashicons-format-aside',
			'rewrite'     => [ 'slug' => 'listing' ],

			'labels'      => [
				'name'               => esc_html__( 'Listings', 'hivepress' ),
				'singular_name'      => esc_html__( 'Listing', 'hivepress' ),
				'add_new_item'       => esc_html__( 'Add New Listing', 'hivepress' ),
				'edit_item'          => esc_html__( 'Edit Listing', 'hivepress' ),
				'new_item'           => esc_html__( 'New Listing', 'hivepress' ),
				'view_item'          => esc_html__( 'View Listing', 'hivepress' ),
				'all_items'          => esc_html__( 'All Listings', 'hivepress' ),
				'search_items'       => esc_html__( 'Search Listings', 'hivepress' ),
				'not_found'          => esc_html__( 'No Listings Found', 'hivepress' ),
				'not_found_in_trash' => esc_html__( 'No Listings Found in Trash', 'hivepress' ),
			],
		],

		'listing_attribute' => [
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'edit.php?post_type=hp_listing',
			'supports'     => [ 'title', 'page-attributes' ],

			'labels'       => [
				'name'               => esc_html__( 'Attributes', 'hivepress' ),
				'singular_name'      => esc_html__( 'Attribute', 'hivepress' ),
				'add_new_item'       => esc_html__( 'Add New Attribute', 'hivepress' ),
				'edit_item'          => esc_html__( 'Edit Attribute', 'hivepress' ),
				'new_item'           => esc_html__( 'New Attribute', 'hivepress' ),
				'view_item'          => esc_html__( 'View Attribute', 'hivepress' ),
				'all_items'          => esc_html__( 'Attributes', 'hivepress' ),
				'search_items'       => esc_html__( 'Search Attributes', 'hivepress' ),
				'not_found'          => esc_html__( 'No Attributes Found', 'hivepress' ),
				'not_found_in_trash' => esc_html__( 'No Attributes Found in Trash', 'hivepress' ),
			],
		],
	],

	// Taxonomies.
	'taxonomies'  => [
		'listing_category' => [
			'object_type' => 'listing',

			'args'        => [
				'hierarchical' => true,
				'rewrite'      => [ 'slug' => 'listing-category' ],
			],
		],
	],

	// Meta boxes.
	'meta_boxes'  => [
		'listing_attributes' => [
			'title'  => esc_html__( 'Attributes', 'hivepress' ),
			'screen' => 'listing',
		],
	],

	// Image sizes.
	'image_sizes' => [],

	// Styles.
	'styles'      => [
		'fontawesome'       => [
			'handle' => 'fontawesome',
			'src'    => HP_CORE_URL . '/assets/css/fontawesome.min.css',
		],

		'fontawesome_solid' => [
			'handle' => 'fontawesome-solid',
			'src'    => HP_CORE_URL . '/assets/css/fontawesome-solid.min.css',
		],

		'fancybox'          => [
			'handle' => 'fancybox',
			'src'    => HP_CORE_URL . '/assets/css/fancybox.min.css',
		],

		'slick'             => [
			'handle' => 'slick',
			'src'    => HP_CORE_URL . '/assets/css/slick.min.css',
		],

		'jquery_ui'         => [
			'handle' => 'jquery-ui',
			'src'    => HP_CORE_URL . '/assets/css/jquery-ui.min.css',
		],

		'core_frontend'     => [
			'handle' => 'hp-core-frontend',
			'src'    => HP_CORE_URL . '/assets/css/frontend.min.css',
		],

		'core_backend'      => [
			'handle' => 'hp-core-backend',
			'src'    => HP_CORE_URL . '/assets/css/backend.min.css',
			'admin'  => true,
		],
	],

	// Scripts.
	'scripts'     => [
		'iframe_transport' => [
			'handle' => 'iframe-transport',
			'src'    => HP_CORE_URL . '/assets/js/jquery.iframe-transport.min.js',
		],

		'file_upload'      => [
			'handle' => 'fileupload',
			'src'    => HP_CORE_URL . '/assets/js/jquery.fileupload.min.js',
			'deps'   => [ 'jquery-ui-widget', 'iframe-transport' ],
		],

		'fancybox'         => [
			'handle' => 'fancybox',
			'src'    => HP_CORE_URL . '/assets/js/jquery.fancybox.min.js',
		],

		'slick'            => [
			'handle' => 'slick',
			'src'    => HP_CORE_URL . '/assets/js/slick.min.js',
		],

		'sticky_sidebar'   => [
			'handle' => 'sticky-sidebar',
			'src'    => HP_CORE_URL . '/assets/js/jquery.sticky-sidebar.min.js',
		],

		'core_frontend'    => [
			'handle' => 'hp-core-frontend',
			'src'    => HP_CORE_URL . '/assets/js/frontend.min.js',
			'deps'   => [ 'jquery', 'jquery-ui-sortable', 'fileupload', 'fancybox', 'slick', 'sticky-sidebar' ],
		],

		'core_backend'     => [
			'handle' => 'hp-core-backend',
			'src'    => HP_CORE_URL . '/assets/js/backend.min.js',
			'deps'   => [ 'jquery' ],
			'admin'  => true,
		],
	],
];
