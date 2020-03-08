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
	'listing_settings'          => [
		'title'  => esc_html__( 'Settings', 'hivepress' ),
		'screen' => 'listing',
		'model'  => 'listing',

		'fields' => [
			'featured'      => [
				'label'   => esc_html_x( 'Featured', 'listing', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'make_listing_featured' ),
				'type'    => 'checkbox',
				'_order'  => 10,
			],

			'verified'      => [
				'label'   => esc_html_x( 'Verified', 'listing', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'mark_listing_as_verified' ),
				'type'    => 'checkbox',
				'_order'  => 20,
			],

			'featured_time' => [
				'label'       => esc_html__( 'Featuring Date', 'hivepress' ),
				'description' => hivepress()->translator->get_string( 'set_date_on_which_listing_not_featured' ),
				'type'        => 'date',
				'format'      => 'U',
				'_order'      => 30,
			],

			'expired_time'  => [
				'label'       => esc_html__( 'Expiration Date', 'hivepress' ),
				'description' => hivepress()->translator->get_string( 'set_date_on_which_listing_expired' ),
				'type'        => 'date',
				'format'      => 'U',
				'_order'      => 40,
			],
		],
	],

	'listing_attributes'        => [
		'title'  => esc_html__( 'Attributes', 'hivepress' ),
		'screen' => 'listing',
		'model'  => 'listing',
		'fields' => [],
	],

	'listing_attribute_edit'    => [
		'title'  => esc_html__( 'Editing', 'hivepress' ),
		'screen' => 'listing_attribute',
		'model'  => 'listing',

		'fields' => [
			'editable'        => [
				'label'   => esc_html_x( 'Editable', 'attribute', 'hivepress' ),
				'caption' => esc_html__( 'Allow front-end editing', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 1,
			],

			'edit_field_type' => [
				'label'       => esc_html__( 'Field Type', 'hivepress' ),
				'type'        => 'select',
				'options'     => 'fields',
				'option_args' => [ 'editable' => true ],
				'required'    => true,
				'_order'      => 100,
			],
		],
	],

	'listing_attribute_search'  => [
		'title'  => esc_html_x( 'Search', 'noun', 'hivepress' ),
		'screen' => 'listing_attribute',
		'model'  => 'listing',

		'fields' => [
			'filterable'        => [
				'label'   => esc_html_x( 'Searchable', 'attribute', 'hivepress' ),
				'caption' => esc_html__( 'Display in the search form', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 10,
			],

			'sortable'          => [
				'label'   => esc_html_x( 'Sortable', 'attribute', 'hivepress' ),
				'caption' => esc_html__( 'Display as a sorting option', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 20,
			],

			'search_field_type' => [
				'label'       => esc_html__( 'Field Type', 'hivepress' ),
				'type'        => 'select',
				'options'     => 'fields',
				'option_args' => [ 'filterable' => true ],
				'_order'      => 100,
			],
		],
	],

	'listing_attribute_display' => [
		'title'  => esc_html_x( 'Display', 'noun', 'hivepress' ),
		'screen' => 'listing_attribute',
		'model'  => 'listing',

		'fields' => [
			'display_areas'  => [
				'label'       => esc_html__( 'Areas', 'hivepress' ),
				'description' => esc_html__( 'Choose the template areas where you want to display this attribute.', 'hivepress' ),
				'type'        => 'checkboxes',
				'_order'      => 10,

				'options'     => [
					'view_block_primary'   => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
					'view_block_secondary' => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
					'view_page_primary'    => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
					'view_page_secondary'  => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
				],
			],

			'display_format' => [
				'label'       => esc_html__( 'Format', 'hivepress' ),
				'description' => esc_html__( 'Set the attribute display format.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%label%, %value%' ),
				'type'        => 'text',
				'max_length'  => 2048,
				'default'     => '%value%',
				'html'        => true,
				'_order'      => 20,
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
				'label'   => esc_html_x( 'Display', 'noun', 'hivepress' ),
				'caption' => esc_html__( 'Display subcategories', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 30,
			],
		],
	],

	'listing_option_settings'   => [
		'screen' => [],

		'fields' => [
			'sort_order' => [
				'label'     => esc_html_x( 'Order', 'sort priority', 'hivepress' ),
				'type'      => 'number',
				'min_value' => 0,
				'default'   => 0,
				'required'  => true,
				'_order'    => 10,
			],
		],
	],

	'vendor_settings'           => [
		'title'  => esc_html__( 'Settings', 'hivepress' ),
		'screen' => 'vendor',
		'model'  => 'vendor',
		'fields' => [],
	],

	'vendor_attributes'         => [
		'title'  => esc_html__( 'Attributes', 'hivepress' ),
		'screen' => 'vendor',
		'model'  => 'vendor',
		'fields' => [],
	],

	'vendor_attribute_edit'     => [
		'title'  => esc_html__( 'Editing', 'hivepress' ),
		'screen' => 'vendor_attribute',
		'model'  => 'vendor',

		'fields' => [
			'editable'        => [
				'label'   => esc_html_x( 'Editable', 'attribute', 'hivepress' ),
				'caption' => esc_html__( 'Allow front-end editing', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 1,
			],

			'edit_field_type' => [
				'label'       => esc_html__( 'Field Type', 'hivepress' ),
				'type'        => 'select',
				'options'     => 'fields',
				'option_args' => [ 'editable' => true ],
				'required'    => true,
				'_order'      => 100,
			],
		],
	],

	'vendor_attribute_search'   => [
		'title'  => esc_html_x( 'Search', 'noun', 'hivepress' ),
		'screen' => 'vendor_attribute',
		'model'  => 'vendor',

		'fields' => [
			'filterable'        => [
				'label'   => esc_html_x( 'Searchable', 'attribute', 'hivepress' ),
				'caption' => esc_html__( 'Display in the search form', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 10,
			],

			'sortable'          => [
				'label'   => esc_html_x( 'Sortable', 'attribute', 'hivepress' ),
				'caption' => esc_html__( 'Display as a sorting option', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 20,
			],

			'search_field_type' => [
				'label'       => esc_html__( 'Field Type', 'hivepress' ),
				'type'        => 'select',
				'options'     => 'fields',
				'option_args' => [ 'filterable' => true ],
				'_order'      => 100,
			],
		],
	],

	'vendor_attribute_display'  => [
		'title'  => esc_html_x( 'Display', 'noun', 'hivepress' ),
		'screen' => 'vendor_attribute',
		'model'  => 'vendor',

		'fields' => [
			'display_areas'  => [
				'label'       => esc_html__( 'Areas', 'hivepress' ),
				'description' => esc_html__( 'Choose the template areas where you want to display this attribute.', 'hivepress' ),
				'type'        => 'checkboxes',
				'_order'      => 10,

				'options'     => [
					'view_block_primary'   => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
					'view_block_secondary' => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
					'view_page_primary'    => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
					'view_page_secondary'  => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
				],
			],

			'display_format' => [
				'label'       => esc_html__( 'Format', 'hivepress' ),
				'description' => esc_html__( 'Set the attribute display format.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%label%, %value%' ),
				'type'        => 'text',
				'max_length'  => 2048,
				'default'     => '%value%',
				'html'        => true,
				'_order'      => 20,
			],
		],
	],

	'vendor_option_settings'    => [
		'screen' => [],

		'fields' => [
			'sort_order' => [
				'label'     => esc_html_x( 'Order', 'sort priority', 'hivepress' ),
				'type'      => 'number',
				'min_value' => 0,
				'default'   => 0,
				'required'  => true,
				'_order'    => 10,
			],
		],
	],
];
