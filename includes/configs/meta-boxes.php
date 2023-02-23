<?php
/**
 * Meta boxes configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'template_settings'         => [
		'title'   => hivepress()->translator->get_string( 'settings' ),
		'screen'  => 'template',
		'context' => 'side',

		'fields'  => [
			'template' => [
				'label'    => esc_html__( 'Template', 'hivepress' ),
				'type'     => 'select',
				'options'  => 'templates',
				'required' => true,
				'_alias'   => 'post_name',
				'_order'   => 10,
			],
		],
	],

	'email_settings'            => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'email',

		'fields' => [
			'event' => [
				'label'    => esc_html__( 'Event', 'hivepress' ),
				'type'     => 'select',
				'options'  => 'emails',
				'required' => true,
				'_alias'   => 'post_name',
				'_order'   => 10,
			],
		],
	],

	'email_details'             => [
		'title'  => hivepress()->translator->get_string( 'details' ),
		'screen' => 'email',
		'blocks' => [],
	],

	'listing_settings'          => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'listing',
		'model'  => 'listing',

		'fields' => [
			'vendor'        => [
				'label'       => hivepress()->translator->get_string( 'vendor' ),
				'type'        => 'select',
				'options'     => 'posts',
				'option_args' => [ 'post_type' => 'hp_vendor' ],
				'source'      => hivepress()->router->get_url( 'vendors_resource' ),
				'_alias'      => 'post_parent',
				'_order'      => 10,
			],

			'verified'      => [
				'label'   => esc_html_x( 'Verified', 'listing', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'mark_listing_as_verified' ),
				'type'    => 'checkbox',
				'_order'  => 20,
			],

			'featured'      => [
				'label'   => esc_html_x( 'Featured', 'listing', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'make_listing_featured' ),
				'type'    => 'checkbox',
				'_order'  => 30,
			],

			'featured_time' => [
				'label'       => esc_html__( 'Featuring Date', 'hivepress' ),
				'description' => hivepress()->translator->get_string( 'set_date_on_which_listing_not_featured' ),
				'type'        => 'date',
				'format'      => 'U',
				'_parent'     => 'featured',
				'_order'      => 40,
			],

			'expired_time'  => [
				'label'       => hivepress()->translator->get_string( 'expiration_date' ),
				'description' => hivepress()->translator->get_string( 'set_date_on_which_listing_expired' ),
				'type'        => 'date',
				'format'      => 'U',
				'_order'      => 50,
			],
		],
	],

	'listing_images'            => [
		'title'  => hivepress()->translator->get_string( 'images' ),
		'screen' => 'listing',
		'model'  => 'listing',

		'fields' => [
			'images' => [
				'caption'   => hivepress()->translator->get_string( 'select_images' ),
				'type'      => 'attachment_upload',
				'multiple'  => true,
				'max_files' => 10,
				'formats'   => [ 'jpg', 'jpeg', 'png' ],
				'_order'    => 10,
			],
		],
	],

	'listing_category_settings' => [
		'screen' => 'listing_category',

		'fields' => [
			'image'                 => [
				'label'   => esc_html__( 'Image', 'hivepress' ),
				'caption' => esc_html__( 'Select Image', 'hivepress' ),
				'type'    => 'attachment_select',
				'formats' => [ 'jpg', 'jpeg', 'png' ],
				'_order'  => 10,
			],

			'sort_order'            => [
				'label'     => esc_html_x( 'Order', 'sort priority', 'hivepress' ),
				'type'      => 'number',
				'min_value' => 0,
				'default'   => 0,
				'required'  => true,
				'_order'    => 20,
			],

			'display_subcategories' => [
				'label'   => hivepress()->translator->get_string( 'display_noun' ),
				'caption' => esc_html__( 'Display subcategories instead of listings', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 30,
			],
		],
	],

	'vendor_settings'           => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'vendor',
		'model'  => 'vendor',

		'fields' => [
			'user'     => [
				'label'    => hivepress()->translator->get_string( 'user' ),
				'type'     => 'select',
				'options'  => 'users',
				'source'   => hivepress()->router->get_url( 'users_resource' ),
				'required' => true,
				'_alias'   => 'post_author',
				'_order'   => 10,
			],

			'verified' => [
				'label'   => esc_html_x( 'Verified', 'vendor', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'mark_vendor_as_verified' ),
				'type'    => 'checkbox',
				'_order'  => 20,
			],
		],
	],

	'user_settings'             => [
		'title'  => hivepress()->translator->get_string( 'settings' ),
		'screen' => 'user',
		'model'  => 'user',

		'fields' => [
			'verified' => [
				'label'   => esc_html_x( 'Verified', 'user', 'hivepress' ),
				'caption' => esc_html__( 'Mark this user as verified', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 10,
			],
		],
	],
];
