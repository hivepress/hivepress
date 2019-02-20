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

			'sections' => [
				'display'    => [
					'title'  => esc_html__( 'Display', 'hivepress' ),
					'order'  => 10,

					'fields' => [
						'page_listings'     => [
							'label'       => esc_html__( 'Listings Page', 'hivepress' ),
							'description' => esc_html__( 'Choose a page that displays all listings.', 'hivepress' ),
							'type'        => 'select',
							'options'     => 'posts',
							'post_type'   => 'page',
							'order'       => 10,
						],

						'page_listings_display_subcategories' => [
							'label'   => esc_html__( 'Listings Page Display', 'hivepress' ),
							'caption' => esc_html__( 'Display subcategories', 'hivepress' ),
							'type'    => 'checkbox',
							'order'   => 20,
						],

						'listings_per_page' => [
							'label'     => esc_html__( 'Listings per Page', 'hivepress' ),
							'type'      => 'number',
							'default'   => 10,
							'min_value' => 1,
							'required'  => true,
							'order'     => 30,
						],
					],
				],

				'submission' => [
					'title'  => esc_html__( 'Submission', 'hivepress' ),
					'order'  => 20,

					'fields' => [
						'page_listing_submission_terms' => [
							'label'       => esc_html__( 'Submission Terms Page', 'hivepress' ),
							'description' => esc_html__( 'Choose a page with terms that user has to accept before submitting a new listing.', 'hivepress' ),
							'type'        => 'select',
							'options'     => 'posts',
							'post_type'   => 'page',
							'order'       => 10,
						],

						'listing_enable_moderation'     => [
							'label'   => esc_html__( 'Moderation', 'hivepress' ),
							'caption' => esc_html__( 'Manually approve new listings', 'hivepress' ),
							'type'    => 'checkbox',
							'default' => true,
							'order'   => 20,
						],
					],
				],

				'emails'     => [
					'title'  => esc_html__( 'Emails', 'hivepress' ),
					'order'  => 30,

					'fields' => [
						'email_listing_approve' => [
							'label'       => esc_html__( 'Listing Approved', 'hivepress' ),
							'description' => esc_html__( 'This email is sent to users when listing is approved, the following placeholders are available: %user_name%, %listing_title%, %listing_url%.', 'hivepress' ),
							'type'        => 'textarea',
							'default'     => hp_sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has been approved, click on the following link to view it: %listing_url%', 'hivepress' ) ),
							'required'    => true,
							'order'       => 10,
						],

						'email_listing_reject'  => [
							'label'       => esc_html__( 'Listing Rejected', 'hivepress' ),
							'description' => esc_html__( 'This email is sent to users when listing is rejected, the following placeholders are available: %user_name%, %listing_title%.', 'hivepress' ),
							'type'        => 'textarea',
							'default'     => hp_sanitize_html( __( 'Hi, %user_name%! Unfortunately, your listing "%listing_title%" has been rejected.', 'hivepress' ) ),
							'required'    => true,
							'order'       => 20,
						],
					],
				],
			],
		],

		'users'        => [
			'title'    => esc_html__( 'Users', 'hivepress' ),
			'order'    => 20,

			'sections' => [
				'registration' => [
					'title'  => esc_html__( 'Registration', 'hivepress' ),
					'order'  => 10,

					'fields' => [
						'page_user_registration_terms' => [
							'label'       => esc_html__( 'Registration Terms Page', 'hivepress' ),
							'description' => esc_html__( 'Choose a page with terms that user has to accept before registering.', 'hivepress' ),
							'type'        => 'select',
							'options'     => 'posts',
							'post_type'   => 'page',
							'order'       => 10,
						],
					],
				],

				'emails'       => [
					'title'  => esc_html__( 'Emails', 'hivepress' ),
					'order'  => 20,

					'fields' => [
						'email_user_register'         => [
							'label'       => esc_html__( 'User Registered', 'hivepress' ),
							'description' => esc_html__( 'This email is sent to users after registration, the following placeholders are available: %1$user_name%, %2$user_password%.', 'hivepress' ),
							'type'        => 'textarea',
							'default'     => hp_sanitize_html( __( "Hi, %1\$user_name%! Thank you for registering, here's your password: %2\$user_password%", 'hivepress' ) ),
							'required'    => true,
							'order'       => 10,
						],

						'email_user_request_password' => [
							'label'       => esc_html__( 'Password Reset', 'hivepress' ),
							'description' => esc_html__( 'This email is sent to users when new password is requested, the following placeholders are available: %user_name%, %password_reset_url%.', 'hivepress' ),
							'type'        => 'textarea',
							'default'     => hp_sanitize_html( __( 'Hi, %user_name%! Please click on the following link to set a new password: %password_reset_url%', 'hivepress' ) ),
							'required'    => true,
							'order'       => 20,
						],
					],
				],
			],
		],

		'integrations' => [
			'title'    => esc_html__( 'Integrations', 'hivepress' ),
			'order'    => 100,

			'sections' => [
				'recaptcha' => [
					'title'  => 'reCAPTCHA',
					'order'  => 10,

					'fields' => [
						'recaptcha_site_key'   => [
							'label'      => esc_html__( 'Site Key', 'hivepress' ),
							'type'       => 'text',
							'max_length' => 256,
							'order'      => 10,
						],

						'recaptcha_secret_key' => [
							'label'      => esc_html__( 'Secret Key', 'hivepress' ),
							'type'       => 'text',
							'max_length' => 256,
							'order'      => 20,
						],

						'recaptcha_forms'      => [
							'label'   => esc_html__( 'Protected Forms', 'hivepress' ),
							'type'    => 'checkboxes',
							'options' => 'forms',
							'order'   => 30,
						],
					],
				],
			],
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
		'listing_attributes'         => [
			'title'  => esc_html__( 'Attributes', 'hivepress' ),
			'screen' => 'listing',
			'fields' => [],
		],

		'listing_attribute_settings' => [
			'title'  => esc_html__( 'Settings', 'hivepress' ),
			'screen' => 'listing_attribute',
			'fields' => [
				'editable'   => [
					'label'   => esc_html__( 'Editable', 'hivepress' ),
					'caption' => esc_html__( 'Add to the front-end editor', 'hivepress' ),
					'type'    => 'checkbox',
					'order'   => 70,
				],

				'required'   => [
					'label'   => esc_html__( 'Required', 'hivepress' ),
					'caption' => esc_html__( 'Make this attribute required', 'hivepress' ),
					'type'    => 'checkbox',
					'order'   => 80,
				],

				'filterable' => [
					'label'   => esc_html__( 'Searchable', 'hivepress' ),
					'caption' => esc_html__( 'Add to the search filter', 'hivepress' ),
					'type'    => 'checkbox',
					'order'   => 90,
				],

				'sortable'   => [
					'label'   => esc_html__( 'Sortable', 'hivepress' ),
					'caption' => esc_html__( 'Add to the sorting options', 'hivepress' ),
					'type'    => 'checkbox',
					'order'   => 100,
				],
			],
		],

		'listing_category_settings'  => [
			'screen' => 'listing_category',

			'fields' => [
				'image'                 => [
					'label'   => esc_html__( 'Image', 'hivepress' ),
					'caption' => esc_html__( 'Select Image', 'hivepress' ),
					'type'    => 'file_select',
					'order'   => 10,
				],

				'order'                 => [
					'label'     => esc_html__( 'Order', 'hivepress' ),
					'type'      => 'number',
					'min_value' => 0,
					'order'     => 20,
				],

				'display_subcategories' => [
					'label'   => esc_html__( 'Display', 'hivepress' ),
					'caption' => esc_html__( 'Display subcategories', 'hivepress' ),
					'type'    => 'checkbox',
					'order'   => 30,
				],
			],
		],
	],

	// Image sizes.
	'image_sizes' => [],

	// Templates.
	'templates'   => [],

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

		'select2'           => [
			'handle' => 'select2',
			'src'    => HP_CORE_URL . '/assets/css/select2.min.css',
		],

		'grid'              => [
			'handle' => 'hp-grid',
			'src'    => HP_CORE_URL . '/assets/css/grid.min.css',
			'editor' => true,
		],

		'core_frontend'     => [
			'handle' => 'hp-core-frontend',
			'src'    => HP_CORE_URL . '/assets/css/frontend.min.css',
			'editor' => true,
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

		'select2'          => [
			'handle' => 'select2',
			'src'    => HP_CORE_URL . '/assets/js/select2.min.js',
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
