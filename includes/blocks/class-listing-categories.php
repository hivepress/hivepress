<?php
/**
 * Listing categories block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing categories block class.
 *
 * @class Listing_Categories
 */
class Listing_Categories extends Block {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var string
	 */
	protected static $settings = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Listing Categories', 'hivepress' ),
				'settings' => [
					'columns' => [
						'label'   => esc_html__( 'Columns', 'hivepress' ),
						'type'    => 'select',
						'default' => 3,
						'order'   => 10,
						'options' => [
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],

					'number'  => [
						'label'     => esc_html__( 'Number', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'order'     => 20,
					],

					'parent'  => [
						'label'    => esc_html__( 'Parent', 'hivepress' ),
						'type'     => 'select',
						'options'  => 'terms',
						'taxonomy' => 'hp_listing_category',
						'default'  => '',
						'order'    => 30,
					],

					'order'   => [
						'label'   => esc_html__( 'Order', 'hivepress' ),
						'type'    => 'select',
						'default' => '',
						'order'   => 40,
						'options' => [
							''      => '&mdash;',
							'name'  => esc_html__( 'Name', 'hivepress' ),
							'count' => esc_html__( 'Count', 'hivepress' ),
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get column width.
		$columns      = absint( $this->get_attribute( 'columns' ) );
		$column_width = 12;

		if ( $columns > 0 && $columns <= 12 ) {
			$column_width = round( $column_width / $columns );
		}

		// Set query arguments.
		$query_args = [
			'taxonomy'   => 'hp_listing_category',
			'hide_empty' => false,
			'number'     => absint( $this->get_attribute( 'number' ) ),
			'parent'     => absint( $this->get_attribute( 'parent' ) ),
		];

		// Get order.
		if ( 'name' === $this->get_attribute( 'order' ) ) {
			$query_args['orderby'] = 'name';
		} elseif ( 'count' === $this->get_attribute( 'order' ) ) {
			$query_args['orderby'] = 'count';
			$query_args['order']   = 'DESC';
		} else {
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'ASC';
			$query_args['meta_key'] = 'hp_order';
		}

		// Query categories.
		$categories = get_terms( $query_args );

		// Render categories.
		if ( ! empty( $categories ) ) {
			$output  = '<div ' . hp\html_attributes( $this->get_attribute( 'attributes' ) ) . '>';
			$output .= '<div class="hp-row">';

			foreach ( $categories as $category ) {
				$output .= '<div class="hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

				$output .= ( new Listing_Category(
					[
						'attributes' => [
							'id'            => $category->term_id,
							'template_name' => 'listing_category_summary',
						],
					]
				) )->render();

				$output .= '</div>';
			}

			$output .= '</div>';
			$output .= '</div>';
		}

		return $output;
	}
}
