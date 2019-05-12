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
		'title'    => esc_html__( 'Listings', 'hivepress' ),
		'order'    => 10,

		'sections' => [
			'display'    => [
				'title'  => esc_html__( 'Display', 'hivepress' ),
				'order'  => 10,

				'fields' => [
					'page_listings'                       => [
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

					'listings_per_page'                   => [
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
						'description' => esc_html__( 'This email is sent to users when listing is approved, the following tokens are available: %user_name%, %listing_title%, %listing_url%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has been approved, click on the following link to view it: %listing_url%', 'hivepress' ) ),
						'required'    => true,
						'order'       => 10,
					],

					'email_listing_reject'  => [
						'label'       => esc_html__( 'Listing Rejected', 'hivepress' ),
						'description' => esc_html__( 'This email is sent to users when listing is rejected, the following tokens are available: %user_name%, %listing_title%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Unfortunately, your listing "%listing_title%" has been rejected.', 'hivepress' ) ),
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
						'description' => esc_html__( 'This email is sent to users after registration, the following tokens are available: %1$user_name%, %2$user_password%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( "Hi, %1\$user_name%! Thank you for registering, here's your password: %2\$user_password%", 'hivepress' ) ),
						'required'    => true,
						'order'       => 10,
					],

					'email_user_request_password' => [
						'label'       => esc_html__( 'Password Reset', 'hivepress' ),
						'description' => esc_html__( 'This email is sent to users when new password is requested, the following tokens are available: %user_name%, %password_reset_url%.', 'hivepress' ),
						'type'        => 'textarea',
						'default'     => hp\sanitize_html( __( 'Hi, %user_name%! Please click on the following link to set a new password: %password_reset_url%', 'hivepress' ) ),
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
];
