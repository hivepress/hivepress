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
		'order'    => 10,

		'sections' => [
			'display'    => [
				'title'  => esc_html__( 'Display', 'hivepress' ),
				'order'  => 10,

				'fields' => [
					'page_listings'                    => [
						'label'       => hivepress()->translator->get_string( 'listings_page' ),
						'description' => hivepress()->translator->get_string( 'choose_page_that_displays_all_listings' ),
						'type'        => 'select',
						'options'     => 'posts',
						'post_type'   => 'page',
						'order'       => 10,
					],

					'page_listings_display_categories' => [
						'label'   => hivepress()->translator->get_string( 'listings_page_display' ),
						'caption' => esc_html__( 'Display categories', 'hivepress' ),
						'type'    => 'checkbox',
						'order'   => 20,
					],

					'listings_per_page'                => [
						'label'     => hivepress()->translator->get_string( 'regular_listings_per_page' ),
						'type'      => 'number',
						'default'   => 8,
						'min_value' => 1,
						'required'  => true,
						'order'     => 30,
					],

					'listings_featured_per_page'       => [
						'label'     => hivepress()->translator->get_string( 'featured_listings_per_page' ),
						'type'      => 'number',
						'default'   => 2,
						'min_value' => 0,
						'required'  => true,
						'order'     => 40,
					],
				],
			],

			'submission' => [
				'title'  => esc_html__( 'Submission', 'hivepress' ),
				'order'  => 20,

				'fields' => [
					'page_listing_submission_terms' => [
						'label'       => esc_html__( 'Submission Terms Page', 'hivepress' ),
						'description' => hivepress()->translator->get_string( 'choose_page_with_listing_submission_terms' ),
						'type'        => 'select',
						'options'     => 'posts',
						'post_type'   => 'page',
						'order'       => 10,
					],

					'listing_enable_submission'     => [
						'label'   => esc_html__( 'Submission', 'hivepress' ),
						'caption' => hivepress()->translator->get_string( 'allow_submitting_listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'order'   => 20,
					],

					'listing_enable_moderation'     => [
						'label'   => hivepress()->translator->get_string( 'moderation' ),
						'caption' => hivepress()->translator->get_string( 'manually_approve_listings' ),
						'type'    => 'checkbox',
						'default' => true,
						'order'   => 30,
					],
				],
			],

			'expiration' => [
				'title'  => esc_html__( 'Expiration', 'hivepress' ),
				'order'  => 30,

				'fields' => [
					'listing_expiration_period' => [
						'label'       => esc_html__( 'Expiration Period', 'hivepress' ),
						'description' => hivepress()->translator->get_string( 'set_number_of_days_until_listing_expires' ),
						'type'        => 'number',
						'min_value'   => 1,
						'order'       => 10,
					],
				],
			],

			'emails'     => [
				'title'  => esc_html__( 'Emails', 'hivepress' ),
				'order'  => 100,

				'fields' => [
					'email_listing_approve' => [
						'label'       => hivepress()->translator->get_string( 'listing_approved' ),
						'description' => esc_html__( 'This email is sent to users when listing is approved, the following tokens are available: %user_name%, %listing_title%, %listing_url%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has been approved, click on the following link to view it: %listing_url%', 'hivepress' ) ),
						'html'        => 'post',
						'required'    => true,
						'autoload'    => false,
						'order'       => 10,
					],

					'email_listing_reject'  => [
						'label'       => hivepress()->translator->get_string( 'listing_rejected' ),
						'description' => esc_html__( 'This email is sent to users when listing is rejected, the following tokens are available: %user_name%, %listing_title%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your listing "%listing_title%" has been rejected.', 'hivepress' ) ),
						'html'        => 'post',
						'required'    => true,
						'autoload'    => false,
						'order'       => 20,
					],

					'email_listing_expire'  => [
						'label'       => hivepress()->translator->get_string( 'listing_expired' ),
						'description' => esc_html__( 'This email is sent to users when listing is expired, the following tokens are available: %user_name%, %listing_title%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has expired.', 'hivepress' ) ),
						'html'        => 'post',
						'required'    => true,
						'autoload'    => false,
						'order'       => 30,
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
				'order'  => 100,

				'fields' => [
					'email_user_register'         => [
						'label'       => esc_html__( 'User Registered', 'hivepress' ),
						'description' => esc_html__( 'This email is sent to users after registration, the following tokens are available: %user_name%, %user_password%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( "Hi, %user_name%! Thank you for registering, here's your password: %user_password%", 'hivepress' ) ),
						'html'        => 'post',
						'required'    => true,
						'autoload'    => false,
						'order'       => 10,
					],

					'email_user_request_password' => [
						'label'       => esc_html__( 'Password Reset', 'hivepress' ),
						'description' => esc_html__( 'This email is sent to users when new password is requested, the following tokens are available: %user_name%, %password_reset_url%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Please click on the following link to set a new password: %password_reset_url%', 'hivepress' ) ),
						'html'        => 'post',
						'required'    => true,
						'autoload'    => false,
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
];
