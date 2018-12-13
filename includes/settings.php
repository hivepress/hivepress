<?php
/**
 * Contains plugin settings.
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$settings = [

	// Admin component.
	'admin'    => [

		// Options.
		'options'       => [
			'integrations' => [
				'name'     => esc_html__( 'Integrations', 'hivepress' ),
				'order'    => 100,
				'sections' => [],
			],
		],

		// Styles.
		'admin_styles'  => [
			'backend' => [
				'handle' => 'hp-core',
				'src'    => HP_CORE_URL . '/assets/css/backend.min.css',
			],
		],

		// Scripts.
		'admin_scripts' => [
			'backend' => [
				'handle' => 'hp-core',
				'src'    => HP_CORE_URL . '/assets/js/backend.min.js',
				'deps'   => [ 'jquery' ],
			],
		],
	],

	// Form component.
	'form'     => [

		// Options.
		'options' => [
			'integrations' => [
				'sections' => [
					'recaptcha' => [
						'name'   => 'reCAPTCHA',
						'order'  => 10,

						'fields' => [
							'recaptcha_site_key'   => [
								'name'  => esc_html__( 'Site Key', 'hivepress' ),
								'type'  => 'text',
								'order' => 10,
							],

							'recaptcha_secret_key' => [
								'name'  => esc_html__( 'Secret Key', 'hivepress' ),
								'type'  => 'text',
								'order' => 20,
							],

							'recaptcha_forms'      => [
								'name'    => esc_html__( 'Protected Forms', 'hivepress' ),
								'type'    => 'checkboxes',
								'options' => 'forms',
								'order'   => 30,
							],
						],
					],
				],
			],
		],

		// Forms.
		'forms'   => [
			'upload_file' => [
				'capability' => 'read',

				'fields'     => [
					'post_id'        => [
						'type' => 'hidden',
					],

					'parent_form_id' => [
						'type'     => 'hidden',
						'required' => true,
					],
				],
			],

			'delete_file' => [
				'capability' => 'read',

				'fields'     => [
					'attachment_id' => [
						'type'     => 'file_upload',
						'required' => true,
					],
				],
			],

			'sort_files'  => [
				'capability' => 'read',

				'fields'     => [
					'attachment_ids' => [
						'type'     => 'file_upload',
						'multiple' => true,
						'required' => true,
					],
				],
			],
		],
	],

	// Email component.
	'email'    => [

		// Templates.
		'templates' => [
			'email' => [
				'path' => 'email',
			],
		],
	],

	// Template component.
	'template' => [

		// Templates.
		'templates' => [
			'page' => [
				'path'  => 'page',

				'areas' => [
					'menu'    => [],
					'popups'  => [],

					'header'  => [
						'menu' => [
							'path'  => 'page/menu',
							'order' => 10,
						],
					],

					'content' => [],

					'footer'  => [
						'popups' => [
							'path'  => 'page/popups',
							'order' => 10,
						],
					],
				],
			],
		],

		// Styles.
		'styles'    => [
			'fontawesome'       => [
				'handle' => 'fontawesome',
				'src'    => HP_CORE_URL . '/assets/css/fontawesome/fontawesome.min.css',
			],

			'fontawesome_solid' => [
				'handle' => 'fontawesome-solid',
				'src'    => HP_CORE_URL . '/assets/css/fontawesome/solid.min.css',
			],

			'jquery_ui'          => [
				'handle' => 'jquery-ui',
				'src'    => HP_CORE_URL . '/assets/css/jquery-ui.min.css',
			],

			'fancybox'          => [
				'handle' => 'fancybox',
				'src'    => HP_CORE_URL . '/assets/css/fancybox.min.css',
			],

			'slick'             => [
				'handle' => 'slick',
				'src'    => HP_CORE_URL . '/assets/css/slick.min.css',
			],

			'grid'              => [
				'handle' => 'hp-grid',
				'src'    => HP_CORE_URL . '/assets/css/grid.min.css',
			],

			'frontend'          => [
				'handle' => 'hp-core',
				'src'    => HP_CORE_URL . '/assets/css/frontend.min.css',
			],
		],

		// Scripts.
		'scripts'   => [
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

			'frontend'         => [
				'handle' => 'hp-core',
				'src'    => HP_CORE_URL . '/assets/js/frontend.min.js',
				'deps'   => [ 'jquery', 'jquery-ui-sortable', 'fileupload', 'fancybox', 'slick', 'sticky-sidebar' ],
				'data'   => [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ],
			],
		],
	],

	// User component.
	'user'     => [

		// Options.
		'options'   => [
			'users' => [
				'name'     => esc_html__( 'Users', 'hivepress' ),
				'order'    => 20,

				'sections' => [
					'registration' => [
						'name'   => esc_html__( 'Registration', 'hivepress' ),
						'order'  => 10,

						'fields' => [
							'page_user_registration_terms' => [
								'name'        => esc_html__( 'Registration Terms Page', 'hivepress' ),
								'description' => esc_html__( 'Choose a page with terms that user has to accept before registering.', 'hivepress' ),
								'type'        => 'select',
								'options'     => 'posts',
								'post_type'   => 'page',
								'order'       => 10,
							],
						],
					],

					'emails'       => [
						'name'   => esc_html__( 'Emails', 'hivepress' ),
						'order'  => 20,

						'fields' => [
							'email_user_register'         => [
								'name'        => esc_html__( 'User Registered', 'hivepress' ),
								'description' => esc_html__( 'This email is sent to users after registration, the following placeholders are available: %1$user_name%, %2$user_password%.', 'hivepress' ),
								'type'        => 'textarea',
								'default'     => hp_sanitize_html( __( "Hi, %1\$user_name%! Thank you for registering, here's your password: %2\$user_password%", 'hivepress' ) ),
								'required'    => true,
								'order'       => 10,
							],

							'email_user_request_password' => [
								'name'        => esc_html__( 'Password Reset', 'hivepress' ),
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
		],

		// Emails.
		'emails'    => [
			'register'         => [
				'subject' => esc_html__( 'Registration Complete', 'hivepress' ),
			],

			'request_password' => [
				'subject' => esc_html__( 'Password Reset', 'hivepress' ),
			],
		],

		// Forms.
		'forms'     => [
			'register'         => [
				'name'             => esc_html__( 'Register User', 'hivepress' ),
				'capability'       => 'login',
				'captcha'          => false,
				'success_redirect' => true,

				'fields'           => [
					'email'    => [
						'name'     => esc_html__( 'Email', 'hivepress' ),
						'type'     => 'email',
						'required' => true,
						'order'    => 10,
					],

					'password' => [
						'name'     => esc_html__( 'Password', 'hivepress' ),
						'type'     => 'password',
						'required' => true,
						'order'    => 20,
					],

					'redirect' => [
						'type' => 'hidden',
					],
				],

				'submit_button'    => [
					'name' => esc_html__( 'Register', 'hivepress' ),
				],
			],

			'login'            => [
				'name'             => esc_html__( 'Login User', 'hivepress' ),
				'capability'       => 'login',
				'captcha'          => false,
				'success_redirect' => true,

				'fields'           => [
					'username' => [
						'name'       => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'order'      => 10,
					],

					'password' => [
						'name'     => esc_html__( 'Password', 'hivepress' ),
						'type'     => 'password',
						'required' => true,
						'order'    => 20,
					],

					'redirect' => [
						'type' => 'hidden',
					],
				],

				'submit_button'    => [
					'name' => esc_html__( 'Sign In', 'hivepress' ),
				],
			],

			'request_password' => [
				'name'            => esc_html__( 'Reset Password', 'hivepress' ),
				'capability'      => 'login',
				'captcha'         => false,
				'success_message' => esc_html__( 'Password reset email has been sent.', 'hivepress' ),

				'fields'          => [
					'username' => [
						'name'       => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'order'      => 10,
					],
				],

				'submit_button'   => [
					'name' => esc_html__( 'Send Email', 'hivepress' ),
				],
			],

			'reset_password'   => [
				'capability'       => 'login',
				'success_redirect' => true,

				'fields'           => [
					'password' => [
						'name'     => esc_html__( 'New Password', 'hivepress' ),
						'type'     => 'password',
						'required' => true,
						'order'    => 10,
					],

					'username' => [
						'type'     => 'hidden',
						'required' => true,
					],

					'key'      => [
						'type'     => 'hidden',
						'required' => true,
					],
				],

				'submit_button'    => [
					'name' => esc_html__( 'Reset Password', 'hivepress' ),
				],
			],

			'update'           => [
				'capability'      => 'read',
				'success_message' => esc_html__( 'Your settings have been updated.', 'hivepress' ),

				'fields'          => [
					'image'            => [
						'name'       => esc_html__( 'Profile Image', 'hivepress' ),
						'label'      => esc_html__( 'Select Image', 'hivepress' ),
						'type'       => 'file_upload',
						'extensions' => [ 'jpg', 'jpeg', 'png' ],
						'order'      => 10,
					],

					'first_name'       => [
						'name'       => esc_html__( 'First Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 64,
						'order'      => 20,
					],

					'last_name'        => [
						'name'       => esc_html__( 'Last Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 64,
						'order'      => 30,
					],

					'description'      => [
						'name'       => esc_html__( 'Profile Info', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'order'      => 40,
					],

					'email'            => [
						'name'     => esc_html__( 'Email', 'hivepress' ),
						'type'     => 'email',
						'required' => true,
						'order'    => 50,
					],

					'new_password'     => [
						'name'  => esc_html__( 'New Password', 'hivepress' ),
						'type'  => 'password',
						'order' => 60,
					],

					'current_password' => [
						'name'  => esc_html__( 'Current Password', 'hivepress' ),
						'type'  => 'password',
						'order' => 70,
					],
				],

				'submit_button'   => [
					'name' => esc_html__( 'Update Settings', 'hivepress' ),
				],
			],

			'delete'           => [
				'capability'       => 'read',
				'success_redirect' => true,

				'fields'           => [
					'password' => [
						'name'     => esc_html__( 'Password', 'hivepress' ),
						'type'     => 'password',
						'required' => true,
						'order'    => 10,
					],
				],

				'submit_button'    => [
					'name' => esc_html__( 'Delete Account', 'hivepress' ),
				],
			],
		],

		// Pages.
		'pages'     => [
			'login'          => [
				'title'      => esc_html__( 'Sign In', 'hivepress' ),
				'regex'      => '^account/login/?$',
				'redirect'   => 'index.php?hp_user_login=1',
				'capability' => 'login',
				'template'   => 'user_login',
			],

			'reset_password' => [
				'title'      => esc_html__( 'Reset Password', 'hivepress' ),
				'regex'      => '^account/reset-password/?$',
				'redirect'   => 'index.php?hp_user_reset_password=1',
				'capability' => 'login',
				'template'   => 'user_reset_password',
			],

			'account'        => [
				'regex'      => '^account/?$',
				'redirect'   => 'index.php?hp_user_account=1',
				'capability' => 'read',
			],

			'settings'       => [
				'title'      => esc_html__( 'My Settings', 'hivepress' ),
				'regex'      => '^account/settings/?$',
				'redirect'   => 'index.php?hp_user_settings=1',
				'capability' => 'read',
				'template'   => 'user_settings',
				'menu'       => 'user_account',
				'order'      => 100,
			],
		],

		// Templates.
		'templates' => [
			'page'                => [
				'areas' => [
					'menu'   => [
						'user_account' => [
							'path'  => 'user/parts/account-link',
							'order' => 10,
						],
					],

					'popups' => [
						'user_register'         => [
							'path' => 'user/parts/register-popup',
						],

						'user_login'            => [
							'path' => 'user/parts/login-popup',
						],

						'user_request_password' => [
							'path' => 'user/parts/request-password-popup',
						],
					],
				],
			],

			'user_login'          => [
				'path'  => 'user/login',

				'areas' => [
					'content' => [
						'title'      => [
							'path'  => 'parts/title',
							'order' => 10,
						],

						'login_form' => [
							'path'  => 'user/parts/login-form',
							'order' => 20,
						],
					],
				],
			],

			'user_reset_password' => [
				'path'  => 'user/reset-password',

				'areas' => [
					'content' => [
						'title'               => [
							'path'  => 'parts/title',
							'order' => 10,
						],

						'reset_password_form' => [
							'path'  => 'user/parts/reset-password-form',
							'order' => 20,
						],
					],
				],
			],

			'user_account'        => [
				'path'  => 'user/account',

				'areas' => [
					'sidebar' => [
						'menu' => [
							'path'  => 'user/account/menu',
							'order' => 10,
						],
					],

					'content' => [
						'title' => [
							'path'  => 'parts/title',
							'order' => 10,
						],
					],
				],
			],

			'user_settings'       => [
				'parent' => 'user_account',

				'areas'  => [
					'content' => [
						'settings_form' => [
							'path'  => 'user/parts/settings-form',
							'order' => 20,
						],
					],
				],
			],
		],
	],

	// Listing component.
	'listing'  => [

		// Options.
		'options'     => [
			'listings' => [
				'name'     => esc_html__( 'Listings', 'hivepress' ),
				'order'    => 10,

				'sections' => [
					'display'    => [
						'name'   => esc_html__( 'Display', 'hivepress' ),
						'order'  => 10,

						'fields' => [
							'page_listings'     => [
								'name'        => esc_html__( 'Listings Page', 'hivepress' ),
								'description' => esc_html__( 'Choose a page that displays all listings.', 'hivepress' ),
								'type'        => 'select',
								'options'     => 'posts',
								'post_type'   => 'page',
								'order'       => 10,
							],

							'page_listings_display_subcategories' => [
								'name'  => esc_html__( 'Listings Page Display', 'hivepress' ),
								'label' => esc_html__( 'Display subcategories', 'hivepress' ),
								'type'  => 'checkbox',
								'order' => 20,
							],

							'listings_per_page' => [
								'name'     => esc_html__( 'Listings per Page', 'hivepress' ),
								'type'     => 'number',
								'default'  => 10,
								'required' => true,
								'order'    => 30,
							],
						],
					],

					'submission' => [
						'name'   => esc_html__( 'Submission', 'hivepress' ),
						'order'  => 20,

						'fields' => [
							'page_listing_submission_terms' => [
								'name'        => esc_html__( 'Submission Terms Page', 'hivepress' ),
								'description' => esc_html__( 'Choose a page with terms that user has to accept before submitting a new listing.', 'hivepress' ),
								'type'        => 'select',
								'options'     => 'posts',
								'post_type'   => 'page',
								'order'       => 10,
							],

							'listing_enable_moderation' => [
								'name'    => esc_html__( 'Moderation', 'hivepress' ),
								'label'   => esc_html__( 'Manually approve new listings', 'hivepress' ),
								'type'    => 'checkbox',
								'default' => '1',
								'order'   => 20,
							],
						],
					],

					'emails'     => [
						'name'   => esc_html__( 'Emails', 'hivepress' ),
						'order'  => 30,

						'fields' => [
							'email_listing_approve' => [
								'name'        => esc_html__( 'Listing Approved', 'hivepress' ),
								'description' => esc_html__( 'This email is sent to users when listing is approved, the following placeholders are available: %user_name%, %listing_title%, %listing_url%.', 'hivepress' ),
								'type'        => 'textarea',
								'default'     => hp_sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has been approved, click on the following link to view it: %listing_url%', 'hivepress' ) ),
								'required'    => true,
								'order'       => 10,
							],

							'email_listing_reject'  => [
								'name'        => esc_html__( 'Listing Rejected', 'hivepress' ),
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
		],

		// Emails.
		'emails'      => [
			'submit'  => [
				'subject' => esc_html__( 'Listing Submitted', 'hivepress' ),
				'message' => hp_sanitize_html( __( 'A new listing "%listing_title%" has been submitted, click on the following link to view it: %listing_url%', 'hivepress' ) ),
			],

			'update'  => [
				'subject' => esc_html__( 'Listing Updated', 'hivepress' ),
				'message' => hp_sanitize_html( __( 'Listing "%listing_title%" %listing_url% has been updated, the following changes were made: %listing_changes%', 'hivepress' ) ),
			],

			'report'  => [
				'subject' => esc_html__( 'Listing Reported', 'hivepress' ),
				'message' => hp_sanitize_html( __( 'Listing "%listing_title%" %listing_url% has been reported for the following reason: %report_reason%', 'hivepress' ) ),
			],

			'approve' => [
				'subject' => esc_html__( 'Listing Approved', 'hivepress' ),
			],

			'reject'  => [
				'subject' => esc_html__( 'Listing Rejected', 'hivepress' ),
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

			'listing_tag'      => [
				'object_type' => 'listing',

				'args'        => [
					'rewrite' => [ 'slug' => 'listing-tag' ],
				],
			],
		],

		// Meta boxes.
		'meta_boxes'  => [
			'attributes'         => [
				'title'  => esc_html__( 'Attributes', 'hivepress' ),
				'screen' => 'listing',
				'fields' => [],
			],

			'attribute_settings' => [
				'title'  => esc_html__( 'Settings', 'hivepress' ),
				'screen' => 'listing_attribute',

				'fields' => [
					'category'   => [
						'name'        => esc_html__( 'Category', 'hivepress' ),
						'description' => esc_html__( 'Choose a category if you want to make this attribute category-specific instead of global.', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'terms',
						'taxonomy'    => 'hp_listing_category',
						'order'       => 10,
					],

					'type'       => [
						'name'        => esc_html__( 'Type', 'hivepress' ),
						'description' => esc_html__( "Choose the attribute type, if there're two types the second will be used for search.", 'hivepress' ),
						'type'        => 'select',
						'order'       => 20,

						'options'     => [
							'text'                 => esc_html__( 'Text', 'hivepress' ),
							'email__text'          => esc_html__( 'Email', 'hivepress' ),
							'number'               => esc_html__( 'Number', 'hivepress' ),
							'number__number_range' => esc_html__( 'Number / Number Range', 'hivepress' ),
							'checkbox'             => esc_html__( 'Checkbox', 'hivepress' ),
							'checkboxes'           => esc_html__( 'Checkboxes', 'hivepress' ),
							'checkboxes__select'   => esc_html__( 'Checkboxes / Select', 'hivepress' ),
							'checkboxes__radio'    => esc_html__( 'Checkboxes / Radio', 'hivepress' ),
							'select'               => esc_html__( 'Select', 'hivepress' ),
							'select__radio'        => esc_html__( 'Select / Radio', 'hivepress' ),
							'select__checkboxes'   => esc_html__( 'Select / Checkboxes', 'hivepress' ),
							'radio'                => esc_html__( 'Radio', 'hivepress' ),
							'radio__select'        => esc_html__( 'Radio / Select', 'hivepress' ),
							'radio__checkboxes'    => esc_html__( 'Radio / Checkboxes', 'hivepress' ),
						],
					],

					'decimals'   => [
						'name'        => esc_html__( 'Decimals', 'hivepress' ),
						'description' => esc_html__( 'Set the number of decimal places allowed for numeric values.', 'hivepress' ),
						'type'        => 'number',
						'default'     => 0,
						'parent'      => [
							'type' => [
								'number',
								'number__number_range',
							],
						],
						'order'       => 30,
					],

					'label'      => [
						'name'        => esc_html__( 'Label', 'hivepress' ),
						'description' => esc_html__( 'Set the checkbox label that will also be used for displaying this attribute.', 'hivepress' ),
						'type'        => 'text',
						'parent'      => [ 'type' => 'checkbox' ],
						'order'       => 40,
					],

					'format'     => [
						'name'        => esc_html__( 'Format', 'hivepress' ),
						'description' => esc_html__( 'Set the attribute display format, the following placeholders are available: %value%.', 'hivepress' ),
						'type'        => 'text',
						'default'     => '%value%',
						'order'       => 50,
					],

					'areas'      => [
						'name'        => esc_html__( 'Areas', 'hivepress' ),
						'description' => esc_html__( 'Choose the template areas where you want to display this attribute.', 'hivepress' ),
						'type'        => 'checkboxes',
						'order'       => 60,

						'options'     => [
							'archive__primary'   => esc_html__( 'Archive page (primary)', 'hivepress' ),
							'archive__secondary' => esc_html__( 'Archive page (secondary)', 'hivepress' ),
							'single__primary'    => esc_html__( 'Single page (primary)', 'hivepress' ),
							'single__secondary'  => esc_html__( 'Single page (secondary)', 'hivepress' ),
						],
					],

					'editable'   => [
						'name'  => esc_html__( 'Editable', 'hivepress' ),
						'label' => esc_html__( 'Add to the front-end editor', 'hivepress' ),
						'type'  => 'checkbox',
						'order' => 70,
					],

					'required'   => [
						'name'   => esc_html__( 'Required', 'hivepress' ),
						'label'  => esc_html__( 'Make this attribute required', 'hivepress' ),
						'type'   => 'checkbox',
						'parent' => 'editable',
						'order'  => 80,
					],

					'filterable' => [
						'name'  => esc_html__( 'Searchable', 'hivepress' ),
						'label' => esc_html__( 'Add to the search filter', 'hivepress' ),
						'type'  => 'checkbox',
						'order' => 90,
					],

					'sortable'   => [
						'name'   => esc_html__( 'Sortable', 'hivepress' ),
						'label'  => esc_html__( 'Add to the sorting options', 'hivepress' ),
						'type'   => 'checkbox',
						'parent' => [
							'type' => [
								'text',
								'number',
								'number__number_range',
							],
						],
						'order'  => 100,
					],
				],
			],

			'category_settings'  => [
				'screen' => 'listing_category',

				'fields' => [
					'image'                 => [
						'name'  => esc_html__( 'Image', 'hivepress' ),
						'label' => esc_html__( 'Select Image', 'hivepress' ),
						'type'  => 'file_select',
						'order' => 10,
					],

					'order'                 => [
						'name'  => esc_html__( 'Order', 'hivepress' ),
						'type'  => 'number',
						'order' => 20,
					],

					'display_subcategories' => [
						'name'  => esc_html__( 'Display', 'hivepress' ),
						'label' => esc_html__( 'Display subcategories', 'hivepress' ),
						'type'  => 'checkbox',
						'order' => 30,
					],
				],
			],
		],

		// Forms.
		'forms'       => [
			'search' => [
				'action'        => home_url(),
				'method'        => 'GET',

				'fields'        => [
					's'         => [
						'placeholder' => esc_html__( 'Keywords', 'hivepress' ),
						'type'        => 'search',
						'max_length'  => 256,
						'order'       => 10,
					],

					'post_type' => [
						'type'     => 'hidden',
						'default'  => 'hp_listing',
						'required' => true,
					],
				],

				'submit_button' => [
					'name' => esc_html__( 'Search', 'hivepress' ),
				],
			],

			'filter' => [
				'action'        => home_url(),
				'method'        => 'GET',
				'parent'        => [ 'search', 'sort' ],

				'fields'        => [
					'category'  => [
						'type' => 'hidden',
					],

					'post_type' => [
						'type'     => 'hidden',
						'default'  => 'hp_listing',
						'required' => true,
					],
				],

				'submit_button' => [
					'name' => esc_html__( 'Filter', 'hivepress' ),
				],
			],

			'sort'   => [
				'action'        => home_url(),
				'method'        => 'GET',
				'parent'        => [ 'search', 'filter' ],
				'submit_button' => false,

				'fields'        => [
					'sort'      => [
						'name'    => esc_html__( 'Sort by:', 'hivepress' ),
						'type'    => 'select',
						'options' => [],
						'order'   => 10,
					],

					'category'  => [
						'type' => 'hidden',
					],

					'post_type' => [
						'type'     => 'hidden',
						'default'  => 'hp_listing',
						'required' => true,
					],
				],
			],

			'submit' => [
				'name'             => esc_html__( 'Submit Listing', 'hivepress' ),
				'capability'       => 'read',
				'captcha'          => false,
				'success_redirect' => true,

				'fields'           => [
					'title'       => [
						'name'       => esc_html__( 'Title', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
						'order'      => 10,
					],

					'description' => [
						'name'       => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
						'order'      => 20,
					],

					'images'      => [
						'name'       => esc_html__( 'Images', 'hivepress' ),
						'label'      => esc_html__( 'Select Images', 'hivepress' ),
						'type'       => 'file_upload',
						'extensions' => [ 'jpg', 'jpeg', 'png' ],
						'multiple'   => true,
						'order'      => 30,
					],

					'post_id'     => [
						'type'     => 'hidden',
						'required' => true,
					],
				],

				'submit_button'    => [
					'name' => esc_html__( 'Submit Listing', 'hivepress' ),
				],
			],

			'update' => [
				'capability'      => 'read',
				'success_message' => esc_html__( 'Listing has been updated.', 'hivepress' ),

				'fields'          => [
					'title'       => [
						'name'       => esc_html__( 'Title', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
						'order'      => 10,
					],

					'description' => [
						'name'       => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'required'   => true,
						'order'      => 20,
					],

					'images'      => [
						'name'       => esc_html__( 'Images', 'hivepress' ),
						'label'      => esc_html__( 'Select Images', 'hivepress' ),
						'type'       => 'file_upload',
						'extensions' => [ 'jpg', 'jpeg', 'png' ],
						'multiple'   => true,
						'order'      => 30,
					],

					'post_id'     => [
						'type'     => 'hidden',
						'required' => true,
					],
				],

				'submit_button'   => [
					'name' => esc_html__( 'Update Listing', 'hivepress' ),
				],
			],

			'delete' => [
				'capability'       => 'read',
				'success_redirect' => true,

				'fields'           => [
					'post_id' => [
						'type'     => 'hidden',
						'required' => true,
					],
				],

				'submit_button'    => [
					'name' => esc_html__( 'Delete Listing', 'hivepress' ),
				],
			],

			'report' => [
				'name'            => esc_html__( 'Report Listing', 'hivepress' ),
				'capability'      => 'read',
				'captcha'         => false,
				'success_message' => esc_html__( 'Listing has been reported.', 'hivepress' ),

				'fields'          => [
					'reason'  => [
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'order'      => 10,
					],

					'post_id' => [
						'type'     => 'hidden',
						'required' => true,
					],
				],

				'submit_button'   => [
					'name' => esc_html__( 'Report Listing', 'hivepress' ),
				],
			],
		],

		// Pages.
		'pages'       => [
			'submission'          => [
				'regex'      => '^submit-listing/?$',
				'redirect'   => 'index.php?hp_listing_submission=1',
				'capability' => 'read',
			],

			'submission_category' => [
				'title'      => esc_html__( 'Select Category', 'hivepress' ),
				'regex'      => '^submit-listing/category/([0-9]+)/?$',
				'redirect'   => 'index.php?hp_listing_submission_category=$matches[1]',
				'capability' => 'read',
				'template'   => 'listing_submission_category',
				'menu'       => 'listing_submission',
				'order'      => 10,
			],

			'submission_details'  => [
				'title'      => esc_html__( 'Add Details', 'hivepress' ),
				'regex'      => '^submit-listing/details/?$',
				'redirect'   => 'index.php?hp_listing_submission_details=1',
				'capability' => 'read',
				'template'   => 'listing_submission_details',
				'menu'       => 'listing_submission',
				'order'      => 20,
			],

			'submission_review'   => [
				'title'      => esc_html__( 'Listing Submitted', 'hivepress' ),
				'regex'      => '^submit-listing/review/([0-9]+)/?$',
				'redirect'   => 'index.php?hp_listing_submission_review=$matches[1]',
				'capability' => 'read',
				'template'   => 'listing_submission_review',
				'menu'       => 'listing_submission',
				'order'      => 30,
			],

			'edits'               => [
				'title'      => esc_html__( 'My Listings', 'hivepress' ),
				'regex'      => '^account/listings/?$',
				'redirect'   => 'index.php?hp_listing_edits=1',
				'capability' => 'read',
				'template'   => 'listing_edits',
				'menu'       => 'user_account',
				'order'      => 10,
			],

			'edit'                => [
				'title'      => esc_html__( 'Edit Listing', 'hivepress' ),
				'regex'      => '^account/listing/([0-9]+)/?$',
				'redirect'   => 'index.php?hp_listing_edit=$matches[1]',
				'capability' => 'read',
				'template'   => 'listing_edit',
			],

			'vendor'              => [
				'regex'    => '^vendor/([^\/]+)/?$',
				'redirect' => 'index.php?hp_listing_vendor=$matches[1]',
				'template' => 'single_vendor',
			],
		],

		// Templates.
		'templates'   => [
			'page'                        => [
				'areas' => [
					'menu' => [
						'listing_submission' => [
							'path'  => 'listing/parts/submission-link',
							'order' => 20,
						],
					],
				],
			],

			'listing_submission'          => [
				'path'  => 'listing/submission',

				'areas' => [
					'content' => [
						'title' => [
							'path'  => 'parts/title',
							'order' => 10,
						],
					],
				],
			],

			'listing_submission_category' => [
				'parent' => 'listing_submission',

				'areas'  => [
					'content' => [
						'loop' => [
							'path'  => 'category/parts/loop-select',
							'order' => 20,
						],
					],
				],
			],

			'listing_submission_details'  => [
				'parent' => 'listing_submission',

				'areas'  => [
					'content' => [
						'submit_form' => [
							'path'  => 'listing/submission/submit-form',
							'order' => 20,
						],
					],
				],
			],

			'listing_submission_review'   => [
				'parent' => 'listing_submission',

				'areas'  => [
					'content' => [
						'review_message' => [
							'path'  => 'listing/submission/review-message',
							'order' => 20,
						],
					],
				],
			],

			'listing_edits'               => [
				'parent' => 'user_account',

				'areas'  => [
					'content' => [
						'loop' => [
							'path'  => 'listing/parts/loop-edit',
							'order' => 20,
						],
					],
				],
			],

			'listing_edit'                => [
				'parent' => 'user_account',

				'areas'  => [
					'content' => [
						'edit_form' => [
							'path'  => 'listing/parts/edit-form',
							'order' => 20,
						],
					],
				],
			],

			'category_archive'            => [
				'path'  => 'category/archive',

				'areas' => [
					'content' => [
						'title' => [
							'path'  => 'parts/title',
							'order' => 10,
						],

						'loop'  => [
							'path'  => 'category/parts/loop-archive',
							'order' => 20,
						],
					],
				],
			],

			'archive_category'            => [
				'path'  => 'category/content-archive',

				'areas' => [
					'preview' => [
						'image' => [
							'path'  => 'category/content-archive/parts/image',
							'order' => 10,
						],
					],

					'summary' => [
						'title' => [
							'path'  => 'category/content-archive/parts/title',
							'order' => 10,
						],

						'count' => [
							'path'  => 'category/parts/count',
							'order' => 20,
						],
					],

					'details' => [
						'description' => [
							'path'  => 'category/parts/description',
							'order' => 10,
						],
					],

					'header'  => [
						'preview' => [
							'path'  => 'category/content-archive/preview',
							'order' => 10,
						],
					],

					'content' => [
						'summary' => [
							'path'  => 'category/content-archive/summary',
							'order' => 10,
						],

						'details' => [
							'path'  => 'category/content-archive/details',
							'order' => 20,
						],
					],
				],
			],

			'listing_archive'             => [
				'path'  => 'listing/archive',

				'areas' => [
					'topbar'  => [
						'result_count'    => [
							'path'  => 'parts/result-count',
							'order' => 10,
						],

						'sorting_options' => [
							'path'  => 'listing/archive/parts/sorting-options',
							'order' => 20,
						],
					],

					'header'  => [
						'search_form' => [
							'path'  => 'listing/parts/search-form',
							'order' => 10,
						],
					],

					'sidebar' => [
						'category_filter' => [
							'path'  => 'listing/archive/category-filter',
							'order' => 10,
						],

						'filter_form'     => [
							'path'  => 'listing/archive/filter-form',
							'order' => 20,
						],
					],

					'content' => [
						'title'      => [
							'path'  => 'parts/title',
							'order' => 10,
						],

						'topbar'     => [
							'path'  => 'listing/archive/topbar',
							'order' => 20,
						],

						'loop'       => [
							'path'  => 'listing/parts/loop-archive',
							'order' => 30,
						],

						'pagination' => [
							'path'  => 'parts/pagination',
							'order' => 40,
						],
					],
				],
			],

			'archive_listing'             => [
				'path'  => 'listing/content-archive',

				'areas' => [
					'preview'    => [
						'image' => [
							'path'  => 'listing/content-archive/parts/image',
							'order' => 10,
						],
					],

					'summary'    => [
						'title'    => [
							'path'  => 'listing/content-archive/parts/title',
							'order' => 10,
						],

						'category' => [
							'path'  => 'listing/parts/category',
							'order' => 20,
						],

						'date'     => [
							'path'  => 'listing/parts/date',
							'order' => 30,
						],
					],

					'details'    => [
						'attributes' => [
							'path'  => 'listing/content-archive/parts/attributes-secondary',
							'order' => 10,
						],
					],

					'properties' => [
						'attributes' => [
							'path'  => 'listing/content-archive/parts/attributes-primary',
							'order' => 10,
						],
					],

					'actions'    => [],

					'header'     => [
						'preview' => [
							'path'  => 'listing/content-archive/preview',
							'order' => 10,
						],
					],

					'content'    => [
						'summary' => [
							'path'  => 'listing/content-archive/summary',
							'order' => 10,
						],

						'details' => [
							'path'  => 'listing/content-archive/details',
							'order' => 20,
						],
					],

					'footer'     => [
						'properties' => [
							'path'  => 'listing/content-archive/properties',
							'order' => 10,
						],

						'actions'    => [
							'path'  => 'listing/content-archive/actions',
							'order' => 20,
						],
					],
				],
			],

			'single_listing'              => [
				'path'  => 'listing/content-single',

				'areas' => [
					'summary'    => [
						'title'    => [
							'path'  => 'listing/content-single/parts/title',
							'order' => 10,
						],

						'category' => [
							'path'  => 'listing/parts/category',
							'order' => 20,
						],

						'date'     => [
							'path'  => 'listing/parts/date',
							'order' => 30,
						],
					],

					'preview'    => [
						'gallery' => [
							'path'  => 'listing/content-single/parts/gallery',
							'order' => 10,
						],
					],

					'details'    => [
						'attributes'  => [
							'path'  => 'listing/content-single/parts/attributes-secondary',
							'order' => 10,
						],

						'description' => [
							'path'  => 'listing/parts/description',
							'order' => 20,
						],

						'tags'        => [
							'path'  => 'listing/parts/tags',
							'order' => 30,
						],
					],

					'properties' => [
						'attributes' => [
							'path'  => 'listing/content-single/parts/attributes-primary',
							'order' => 10,
						],
					],

					'actions'    => [
						'report_form' => [
							'path'  => 'listing/parts/report-form',
							'order' => 100,
						],
					],

					'vendor'     => [
						'vendor' => [
							'template' => 'archive_vendor',
							'order'    => 10,
						],
					],

					'content'    => [
						'summary' => [
							'path'  => 'listing/content-single/summary',
							'order' => 10,
						],

						'preview' => [
							'path'  => 'listing/content-single/preview',
							'order' => 20,
						],

						'details' => [
							'path'  => 'listing/content-single/details',
							'order' => 30,
						],
					],

					'sidebar'    => [
						'properties' => [
							'path'  => 'listing/content-single/properties',
							'order' => 10,
						],

						'actions'    => [
							'path'  => 'listing/content-single/actions',
							'order' => 20,
						],

						'vendor'     => [
							'path'  => 'listing/content-single/vendor',
							'order' => 30,
						],
					],
				],
			],

			'archive_vendor'              => [
				'path'  => 'vendor/content-archive',

				'areas' => [
					'preview' => [
						'image' => [
							'path'  => 'vendor/content-archive/parts/image',
							'order' => 10,
						],
					],

					'summary' => [
						'name' => [
							'path'  => 'vendor/content-archive/parts/name',
							'order' => 10,
						],

						'date' => [
							'path'  => 'vendor/parts/date',
							'order' => 20,
						],
					],

					'header'  => [
						'preview' => [
							'path'  => 'vendor/content-archive/preview',
							'order' => 10,
						],
					],

					'content' => [
						'summary' => [
							'path'  => 'vendor/content-archive/summary',
							'order' => 10,
						],
					],
				],
			],

			'single_vendor'               => [
				'path'  => 'vendor/content-single',

				'areas' => [
					'preview' => [
						'image' => [
							'path'  => 'vendor/content-single/parts/image',
							'order' => 10,
						],
					],

					'summary' => [
						'name' => [
							'path'  => 'vendor/content-single/parts/name',
							'order' => 10,
						],

						'date' => [
							'path'  => 'vendor/parts/date',
							'order' => 20,
						],
					],

					'details' => [
						'description' => [
							'path'  => 'vendor/parts/description',
							'order' => 10,
						],
					],

					'actions' => [],

					'sidebar' => [
						'preview' => [
							'path'  => 'vendor/content-single/preview',
							'order' => 10,
						],

						'summary' => [
							'path'  => 'vendor/content-single/summary',
							'order' => 20,
						],

						'details' => [
							'path'  => 'vendor/content-single/details',
							'order' => 30,
						],

						'actions' => [
							'path'  => 'vendor/content-single/actions',
							'order' => 40,
						],
					],

					'content' => [
						'title' => [
							'path'  => 'vendor/content-single/title',
							'order' => 10,
						],

						'loop'  => [
							'path'  => 'listing/parts/loop-archive',
							'order' => 20,
						],
					],
				],
			],
		],

		// Image sizes.
		'image_sizes' => [
			'medium' => [
				'width'  => 400,
				'height' => 267,
				'crop'   => true,
			],

			'large'  => [
				'width' => 800,
			],
		],

		// Shortcodes.
		'shortcodes'  => [
			'listing_search'     => [],
			'listing_categories' => [],
			'listings'           => [],
		],
	],
];
