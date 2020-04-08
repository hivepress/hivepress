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
				'title'  => esc_html_x( 'Display', 'noun', 'hivepress' ),
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
						'caption' => esc_html__( 'Display categories', 'hivepress' ),
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
						'_order'  => 30,
					],
				],
			],

			'expiration' => [
				'title'  => esc_html__( 'Expiration', 'hivepress' ),
				'_order' => 30,

				'fields' => [
					'listing_expiration_period' => [
						'label'       => esc_html__( 'Expiration Period', 'hivepress' ),
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

			'emails'     => [
				'title'  => esc_html__( 'Emails', 'hivepress' ),
				'_order' => 1000,

				'fields' => [
					'email_listing_approve' => [
						'label'       => hivepress()->translator->get_string( 'listing_approved' ),
						'description' => esc_html__( 'This email is sent to users when listing is approved.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%user_name%, %listing_title%, %listing_url%' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has been approved, click on the following link to view it: %listing_url%', 'hivepress' ) ),
						'max_length'  => 2048,
						'html'        => true,
						'_autoload'   => false,
						'_order'      => 10,
					],

					'email_listing_reject'  => [
						'label'       => hivepress()->translator->get_string( 'listing_rejected' ),
						'description' => esc_html__( 'This email is sent to users when listing is rejected.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%user_name%, %listing_title%' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your listing "%listing_title%" has been rejected.', 'hivepress' ) ),
						'max_length'  => 2048,
						'html'        => true,
						'_autoload'   => false,
						'_order'      => 20,
					],

					'email_listing_expire'  => [
						'label'       => hivepress()->translator->get_string( 'listing_expired' ),
						'description' => esc_html__( 'This email is sent to users when listing is expired.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%user_name%, %listing_title%' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has expired, click on the following link to renew it: %listing_url%', 'hivepress' ) ),
						'max_length'  => 2048,
						'html'        => true,
						'_autoload'   => false,
						'_order'      => 30,
					],
				],
			],
		],
	],

	'vendors'      => [
		'title'    => hivepress()->translator->get_string( 'vendors' ),
		'_order'   => 50,

		'sections' => [
			'display' => [
				'title'  => esc_html_x( 'Display', 'noun', 'hivepress' ),
				'_order' => 10,

				'fields' => [
					'page_vendors'     => [
						'label'       => hivepress()->translator->get_string( 'vendors_page' ),
						'description' => hivepress()->translator->get_string( 'choose_page_that_displays_all_vendors' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'page' ],
						'_order'      => 10,
					],

					'vendors_per_page' => [
						'label'     => hivepress()->translator->get_string( 'regular_vendors_per_page' ),
						'type'      => 'number',
						'default'   => 10,
						'min_value' => 1,
						'required'  => true,
						'_order'    => 20,
					],
				],
			],
		],
	],

	'users'        => [
		'title'    => esc_html__( 'Users', 'hivepress' ),
		'_order'   => 100,

		'sections' => [
			'registration' => [
				'title'  => esc_html__( 'Registration', 'hivepress' ),
				'_order' => 10,

				'fields' => [
					'page_user_registration_terms' => [
						'label'       => esc_html__( 'Registration Terms Page', 'hivepress' ),
						'description' => esc_html__( 'Choose a page with terms that user has to accept before registering.', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'posts',
						'option_args' => [ 'post_type' => 'page' ],
						'_order'      => 10,
					],
				],
			],

			'emails'       => [
				'title'  => esc_html__( 'Emails', 'hivepress' ),
				'_order' => 1000,

				'fields' => [
					'email_user_register'         => [
						'label'       => esc_html__( 'User Registered', 'hivepress' ),
						'description' => esc_html__( 'This email is sent to users after registration.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%user_name%, %user_password%' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( "Hi, %1\$user_name%! Thank you for registering, here's your password: %2\$user_password%", 'hivepress' ) ),
						'max_length'  => 2048,
						'html'        => true,
						'_autoload'   => false,
						'_order'      => 10,
					],

					'email_user_password_request' => [
						'label'       => esc_html__( 'Password Reset', 'hivepress' ),
						'description' => esc_html__( 'This email is sent to users when a password reset is requested.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%user_name%, %password_reset_url%' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Please click on the following link to set a new password: %password_reset_url%', 'hivepress' ) ),
						'max_length'  => 2048,
						'html'        => true,
						'_autoload'   => false,
						'_order'      => 20,
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
					'recaptcha_site_key'   => [
						'label'      => esc_html__( 'Site Key', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 10,
					],

					'recaptcha_secret_key' => [
						'label'      => esc_html__( 'Secret Key', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 20,
					],

					'recaptcha_forms'      => [
						'label'   => esc_html__( 'Protected Forms', 'hivepress' ),
						'type'    => 'checkboxes',
						'options' => 'forms',
						'_order'  => 30,
					],
				],
			],

			'google'    => [
				'title'  => 'Google',
				'fields' => [],
				'_order' => 20,
			],
		],
	],
];
