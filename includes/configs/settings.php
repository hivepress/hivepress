<?php
/**
 * Settings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'listings'     => [
		'title'    => hivepress()->translator->get_string( 'listings' ),
		'_order'   => 10,

		'sections' => [
			'display'    => [
				'title'  => hivepress()->translator->get_string( 'display_noun' ),
				'_order' => 10,

				'fields' => [
					'page_listings'                    => [
						'label'       => hivepress()->translator->get_string( 'listings_page' ),
						'description' => hivepress()->translator->get_string( 'choose_page_that_displays_all_listings' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'page' ],
						'_order'      => 10,
					],

					'page_listings_display_categories' => [
						'label'   => hivepress()->translator->get_string( 'listings_page_display' ),
						'caption' => esc_html__( 'Display categories instead of listings', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 20,
					],

					'listings_per_page'                => [
						'label'     => hivepress()->translator->get_string( 'regular_listings_per_page' ),
						'type'      => 'number',
						'default'   => 8,
						'min_value' => 1,
						'required'  => true,
						'_order'    => 30,
					],

					'listings_featured_per_page'       => [
						'label'     => hivepress()->translator->get_string( 'featured_listings_per_page' ),
						'type'      => 'number',
						'default'   => 2,
						'min_value' => 0,
						'required'  => true,
						'_order'    => 40,
					],

					'listings_related_per_page'        => [
						'label'     => hivepress()->translator->get_string( 'related_listings_per_page' ),
						'type'      => 'number',
						'default'   => 3,
						'min_value' => 0,
						'required'  => true,
						'_order'    => 50,
					],

					'listing_title_format'             => [
						'label'       => hivepress()->translator->get_string( 'title' ),
						'description' => hivepress()->translator->get_string( 'set_title_format_based_on_attributes' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%listing%, %vendor%' ),
						'type'        => 'text',
						'max_length'  => 256,
						'_order'      => 60,
					],

					'listing_enable_image_preview'     => [
						'label'   => hivepress()->translator->get_string( 'images' ),
						'caption' => esc_html__( 'Enable gallery preview', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 70,
					],

					'listing_enable_image_zoom'        => [
						'caption' => esc_html__( 'Enable image zoom', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 80,
					],

					'listing_allow_video'              => [
						'caption' => esc_html__( 'Allow uploading videos', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 90,
					],
				],
			],

			'search'     => [
				'title'  => hivepress()->translator->get_string( 'search_noun' ),
				'_order' => 15,

				'fields' => [
					'listing_search_fields' => [
						'label'    => hivepress()->translator->get_string( 'default_fields' ),
						'type'     => 'select',
						'multiple' => true,
						'default'  => [ 'keyword' ],
						'_order'   => 10,

						'options'  => [
							'keyword'  => hivepress()->translator->get_string( 'keywords' ),
							'category' => hivepress()->translator->get_string( 'categories' ),
						],
					],
				],
			],

			'submission' => [
				'title'  => esc_html__( 'Submission', 'hivepress' ),
				'_order' => 20,

				'fields' => [
					'page_listing_submission_terms' => [
						'label'       => esc_html__( 'Submission Terms Page', 'hivepress' ),
						'description' => hivepress()->translator->get_string( 'choose_page_with_listing_submission_terms' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'page' ],
						'_parent'     => 'listing_enable_submission',
						'_order'      => 10,
					],

					'listing_enable_submission'     => [
						'label'   => esc_html__( 'Submission', 'hivepress' ),
						'caption' => hivepress()->translator->get_string( 'allow_submitting_listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 20,
					],

					'listing_enable_moderation'     => [
						'label'   => hivepress()->translator->get_string( 'moderation' ),
						'caption' => hivepress()->translator->get_string( 'manually_approve_listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'_parent' => 'listing_enable_submission',
						'_order'  => 30,
					],

					'listing_enable_reporting'      => [
						'label'   => esc_html__( 'Reporting', 'hivepress' ),
						'caption' => hivepress()->translator->get_string( 'allow_reporting_listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 40,
					],
				],
			],

			'expiration' => [
				'title'  => esc_html__( 'Expiration', 'hivepress' ),
				'_order' => 30,

				'fields' => [
					'listing_expiration_period' => [
						'label'       => hivepress()->translator->get_string( 'expiration_period' ),
						'description' => hivepress()->translator->get_string( 'set_number_of_days_until_listing_expires' ),
						'type'        => 'number',
						'min_value'   => 1,
						'_order'      => 10,
					],

					'listing_storage_period'    => [
						'label'       => hivepress()->translator->get_string( 'storage_period' ),
						'description' => hivepress()->translator->get_string( 'set_number_of_days_until_listing_deleted' ),
						'type'        => 'number',
						'min_value'   => 1,
						'_order'      => 20,
					],
				],
			],
		],
	],

	'vendors'      => [
		'title'    => hivepress()->translator->get_string( 'vendors' ),
		'_order'   => 50,

		'sections' => [
			'display'      => [
				'title'  => hivepress()->translator->get_string( 'display_noun' ),
				'_order' => 10,

				'fields' => [
					'vendor_enable_display' => [
						'label'   => hivepress()->translator->get_string( 'display_noun' ),
						'caption' => hivepress()->translator->get_string( 'display_vendors_on_frontend' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 10,
					],

					'page_vendors'          => [
						'label'       => hivepress()->translator->get_string( 'vendors_page' ),
						'description' => hivepress()->translator->get_string( 'choose_page_that_displays_all_vendors' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'page' ],
						'_parent'     => 'vendor_enable_display',
						'_order'      => 20,
					],

					'vendors_per_page'      => [
						'label'     => hivepress()->translator->get_string( 'regular_vendors_per_page' ),
						'type'      => 'number',
						'default'   => 10,
						'min_value' => 1,
						'required'  => true,
						'_parent'   => 'vendor_enable_display',
						'_order'    => 30,
					],

					'vendor_display_name'   => [
						'label'       => esc_html_x( 'Display Name', 'noun', 'hivepress' ),
						'placeholder' => esc_html__( 'User Name', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'posts',
						'_order'      => 40,

						'option_args' => [
							'post_type'  => 'hp_vendor_attribute',
							'meta_key'   => 'hp_edit_field_type',
							'meta_value' => 'text',
						],
					],
				],
			],

			'search'       => [
				'title'  => hivepress()->translator->get_string( 'search_noun' ),
				'_order' => 20,

				'fields' => [
					'vendor_search_fields' => [
						'label'    => hivepress()->translator->get_string( 'default_fields' ),
						'type'     => 'select',
						'multiple' => true,
						'default'  => [ 'keyword' ],
						'_order'   => 10,

						'options'  => [
							'keyword'  => hivepress()->translator->get_string( 'keywords' ),
							'category' => hivepress()->translator->get_string( 'categories' ),
						],
					],
				],
			],

			'registration' => [
				'title'  => esc_html__( 'Registration', 'hivepress' ),
				'_order' => 30,

				'fields' => [
					'vendor_enable_registration' => [
						'label'   => esc_html__( 'Registration', 'hivepress' ),
						'caption' => esc_html__( 'Allow direct registration', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 10,
					],
				],
			],
		],
	],

	'users'        => [
		'title'    => esc_html__( 'Users', 'hivepress' ),
		'_order'   => 100,

		'sections' => [
			'display'      => [
				'title'  => hivepress()->translator->get_string( 'display_noun' ),
				'_order' => 10,

				'fields' => [
					'user_enable_display' => [
						'label'   => hivepress()->translator->get_string( 'display_noun' ),
						'caption' => esc_html__( 'Display profiles on the front-end', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 10,
					],

					'user_display_online' => [
						'caption' => esc_html__( 'Display online status for users', 'hivepress' ),
						'type'    => 'checkbox',
						'_parent' => 'user_enable_display',
						'_order'  => 20,
					],

					'user_display_name'   => [
						'label'    => esc_html_x( 'Display Name', 'noun', 'hivepress' ),
						'type'     => 'select',
						'default'  => 'first_name',
						'required' => true,
						'_order'   => 30,

						'options'  => [
							'username'         => esc_html__( 'Username', 'hivepress' ),
							'first_name'       => esc_html__( 'First Name', 'hivepress' ),
							'first_name_extra' => esc_html__( 'First Name and First Letter of Last Name', 'hivepress' ),
							'last_name'        => esc_html__( 'Last Name', 'hivepress' ),
							'last_name_extra'  => esc_html__( 'First Letter of First Name and Last Name', 'hivepress' ),
							'full_name'        => esc_html__( 'Full Name', 'hivepress' ),
						],
					],
				],
			],

			'registration' => [
				'title'  => esc_html__( 'Registration', 'hivepress' ),
				'_order' => 20,

				'fields' => [
					'page_user_registration_terms' => [
						'label'       => esc_html__( 'Registration Terms Page', 'hivepress' ),
						'description' => esc_html__( 'Choose a page with terms that user has to accept before registering.', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'page' ],
						'_parent'     => 'user_enable_registration',
						'_order'      => 10,
					],

					'user_enable_registration'     => [
						'label'   => esc_html__( 'Registration', 'hivepress' ),
						'caption' => esc_html__( 'Allow user registration', 'hivepress' ),
						'type'    => 'checkbox',
						'default' => true,
						'_order'  => 20,
					],

					'user_generate_username'       => [
						'label'   => esc_html__( 'Username', 'hivepress' ),
						'caption' => esc_html__( 'Generate username from the email address', 'hivepress' ),
						'type'    => 'checkbox',
						'default' => true,
						'_parent' => 'user_enable_registration',
						'_order'  => 30,
					],

					'user_verify_email'            => [
						'label'   => esc_html__( 'Email', 'hivepress' ),
						'caption' => esc_html__( 'Require email address verification', 'hivepress' ),
						'type'    => 'checkbox',
						'_parent' => 'user_enable_registration',
						'_order'  => 40,
					],

					'user_disable_backend'         => [
						'label'       => esc_html__( 'Security', 'hivepress' ),
						'description' => esc_html__( 'Check this option to block the WordPress back-end access for regular users.', 'hivepress' ),
						'caption'     => esc_html__( 'Restrict access to the WordPress back-end', 'hivepress' ),
						'type'        => 'checkbox',
						'default'     => true,
						'_parent'     => 'user_enable_registration',
						'_order'      => 50,
					],

					'user_allow_deletion'          => [
						'caption' => esc_html__( 'Allow users to delete their accounts', 'hivepress' ),
						'type'    => 'checkbox',
						'default' => true,
						'_parent' => 'user_enable_registration',
						'_order'  => 60,
					],
				],
			],
		],
	],

	'integrations' => [
		'title'    => esc_html__( 'Integrations', 'hivepress' ),
		'_order'   => 1000,

		'sections' => [
			'recaptcha' => [
				'title'  => 'reCAPTCHA',
				'_order' => 10,

				'fields' => [
					'recaptcha_forms'      => [
						'label'    => esc_html__( 'Protected Forms', 'hivepress' ),
						'type'     => 'select',
						'options'  => 'forms',
						'multiple' => true,
						'_order'   => 10,
					],

					'recaptcha_site_key'   => [
						'label'      => esc_html__( 'Site Key', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'_parent'    => 'recaptcha_forms[]',
						'_order'     => 20,
					],

					'recaptcha_secret_key' => [
						'label'      => hivepress()->translator->get_string( 'secret_key' ),
						'type'       => 'text',
						'max_length' => 256,
						'_parent'    => 'recaptcha_forms[]',
						'_order'     => 30,
					],
				],
			],

			'google'    => [
				'title'  => 'Google',
				'fields' => [],
				'_order' => 20,
			],

			'hivepress' => [
				'title'  => esc_html__( 'HivePress Store', 'hivepress' ),
				'_order' => 1000,

				'fields' => [
					'hivepress_allow_tracking' => [
						'label'       => esc_html__( 'Usage Tracking', 'hivepress' ),
						'caption'     => esc_html__( 'Allow usage tracking', 'hivepress' ),
						/* translators: %s: terms URL. */
						'description' => sprintf( hp\sanitize_html( __( 'Check this option to allow tracking <a href="%s" target="_blank">non-sensitive usage data</a> and help us make HivePress better.', 'hivepress' ) ), 'https://hivepress.io/usage-tracking/' ),
						'type'        => 'checkbox',
						'_order'      => 10,
					],

					'hivepress_license_key'    => [
						'label'       => esc_html__( 'License Keys', 'hivepress' ),
						'description' => esc_html__( 'Please enter the license keys for purchased themes and extensions, each key on a new line.', 'hivepress' ),
						'type'        => 'textarea',
						'max_length'  => 2048,
						'_order'      => 20,
					],
				],
			],
		],
	],
];
