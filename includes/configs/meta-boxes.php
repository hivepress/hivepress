<?php
/**
 * Meta boxes configuration.
 *
 * @package HivePress\Configs
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
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
];
