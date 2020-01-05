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
			'featured' => [
				'label'   => esc_html__( 'Featured', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'make_listing_featured' ),
				'type'    => 'checkbox',
				'_order'  => 10,
			],

			'verified' => [
				'label'   => esc_html__( 'Verified', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'mark_listing_as_verified' ),
				'type'    => 'checkbox',
				'_order'  => 20,
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
				'label'   => esc_html__( 'Editable', 'hivepress' ),
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
				'label'   => esc_html__( 'Searchable', 'hivepress' ),
				'caption' => esc_html__( 'Display in the search form', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 10,
			],

			'sortable'          => [
				'label'   => esc_html__( 'Sortable', 'hivepress' ),
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
		'title'  => esc_html__( 'Display', 'hivepress' ),
		'screen' => 'listing_attribute',
		'model'  => 'listing',

		'fields' => [
			'display_areas'  => [
				'label'       => esc_html__( 'Areas', 'hivepress' ),
				'description' => esc_html__( 'Choose the template areas where you want to display this attribute.', 'hivepress' ),
				'type'        => 'checkboxes',
				'_order'      => 10,

				'options'     => [
					'view_block_primary'   => sprintf( esc_html__( 'Block (%s)', 'hivepress' ), esc_html_x( 'primary', 'area', 'hivepress' ) ),
					'view_block_secondary' => sprintf( esc_html__( 'Block (%s)', 'hivepress' ), esc_html_x( 'secondary', 'area', 'hivepress' ) ),
					'view_page_primary'    => sprintf( esc_html__( 'Page (%s)', 'hivepress' ), esc_html_x( 'primary', 'area', 'hivepress' ) ),
					'view_page_secondary'  => sprintf( esc_html__( 'Page (%s)', 'hivepress' ), esc_html_x( 'secondary', 'area', 'hivepress' ) ),
				],
			],

			'display_format' => [
				'label'       => esc_html__( 'Format', 'hivepress' ),
				'description' => esc_html__( 'Set the attribute display format.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%value%' ),
				'type'        => 'text',
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

			'order'                 => [
				'label'     => esc_html__( 'Order', 'hivepress' ),
				'type'      => 'number',
				'min_value' => 0,
				'_order'    => 20,
			],

			'display_subcategories' => [
				'label'   => esc_html__( 'Display', 'hivepress' ),
				'caption' => esc_html__( 'Display subcategories', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 30,
			],
		],
	],
];
