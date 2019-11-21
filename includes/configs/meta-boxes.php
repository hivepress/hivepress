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

		'fields' => [
			'featured' => [
				'label'   => esc_html__( 'Featured', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'make_listing_featured' ),
				'type'    => 'checkbox',
				'order'   => 10,
			],

			'verified' => [
				'label'   => esc_html__( 'Verified', 'hivepress' ),
				'caption' => hivepress()->translator->get_string( 'mark_listing_as_verified' ),
				'type'    => 'checkbox',
				'order'   => 20,
			],
		],
	],

	'listing_attributes'        => [
		'title'  => esc_html__( 'Attributes', 'hivepress' ),
		'screen' => 'listing',
		'fields' => [],
	],

	'listing_attribute_edit'    => [
		'title'  => esc_html__( 'Editing', 'hivepress' ),
		'screen' => 'listing_attribute',

		'fields' => [
			'editable'        => [
				'label'   => esc_html__( 'Editable', 'hivepress' ),
				'caption' => esc_html__( 'Allow front-end editing', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 1,
			],

			'edit_field_type' => [
				'label'    => esc_html__( 'Field Type', 'hivepress' ),
				'type'     => 'select',
				'options'  => 'fields',
				'required' => true,
				'order'    => 100,
			],
		],
	],

	'listing_attribute_search'  => [
		'title'  => esc_html__( 'Search', 'hivepress' ),
		'screen' => 'listing_attribute',

		'fields' => [
			'filterable'        => [
				'label'   => esc_html__( 'Searchable', 'hivepress' ),
				'caption' => esc_html__( 'Display in the search form', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 10,
			],

			'sortable'          => [
				'label'   => esc_html__( 'Sortable', 'hivepress' ),
				'caption' => esc_html__( 'Display in the sort form', 'hivepress' ),
				'type'    => 'checkbox',
				'order'   => 20,
			],

			'search_field_type' => [
				'label'         => esc_html__( 'Field Type', 'hivepress' ),
				'type'          => 'select',
				'options'       => 'fields',
				'field_filters' => true,
				'order'         => 100,
			],
		],
	],

	'listing_attribute_display' => [
		'title'  => esc_html__( 'Display', 'hivepress' ),
		'screen' => 'listing_attribute',

		'fields' => [
			'display_areas'  => [
				'label'       => esc_html__( 'Areas', 'hivepress' ),
				'description' => esc_html__( 'Choose the template areas where you want to display this attribute.', 'hivepress' ),
				'type'        => 'checkboxes',
				'order'       => 10,
				'options'     => [
					'view_block_primary'   => esc_html__( 'Block (primary)', 'hivepress' ),
					'view_block_secondary' => esc_html__( 'Block (secondary)', 'hivepress' ),
					'view_page_primary'    => esc_html__( 'Page (primary)', 'hivepress' ),
					'view_page_secondary'  => esc_html__( 'Page (secondary)', 'hivepress' ),
				],
			],

			'display_format' => [
				'label'       => esc_html__( 'Format', 'hivepress' ),
				'description' => esc_html__( 'Set the attribute display format, the following tokens are available: %value%.', 'hivepress' ),
				'type'        => 'text',
				'default'     => '%value%',
				'html'        => 'post',
				'order'       => 20,
			],
		],
	],

	'listing_category_settings' => [
		'screen' => 'listing_category',

		'fields' => [
			'image_id'              => [
				'label'        => esc_html__( 'Image', 'hivepress' ),
				'caption'      => esc_html__( 'Select Image', 'hivepress' ),
				'type'         => 'attachment_select',
				'file_formats' => [ 'jpg', 'jpeg', 'png' ],
				'order'        => 10,
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
];
