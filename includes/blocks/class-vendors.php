<?php
/**
 * Vendors block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendors block class.
 *
 * @class Vendors
 */
class Vendors extends Block {

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Vendors number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Vendors order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => hivepress()->translator->get_string( 'vendors' ),

				'settings' => [
					'columns' => [
						'label'    => esc_html_x( 'Columns', 'quantity', 'hivepress' ),
						'type'     => 'select',
						'default'  => 3,
						'required' => true,
						'_order'   => 10,

						'options'  => [
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],

					'number'  => [
						'label'     => esc_html_x( 'Number', 'quantity', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'required'  => true,
						'_order'    => 20,
					],

					'order'   => [
						'label'    => esc_html_x( 'Order', 'sort', 'hivepress' ),
						'type'     => 'select',
						'required' => true,
						'_order'   => 30,

						'options'  => [
							'registered_date' => esc_html_x( 'Date Registered', 'sort order', 'hivepress' ),
							'name'            => esc_html_x( 'Name', 'sort order', 'hivepress' ),
							'random'          => esc_html_x( 'Random', 'sort order', 'hivepress' ),
						],
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		global $wp_query;

		$output = '';

		// Get column width.
		$columns      = absint( $this->columns );
		$column_width = 12;

		if ( $columns > 0 && $columns <= 12 ) {
			$column_width = round( $column_width / $columns );
		}

		// Get vendor query.
		$regular_query = $wp_query;

		if ( ! isset( $this->context['vendors'] ) ) {

			// Set query.
			$query = $this->get_context( 'vendor_query' );

			if ( empty( $query ) ) {
				$query = Models\Vendor::query()->filter(
					[
						'status' => 'publish',
					]
				)->limit( $this->number );

				// Set order.
				if ( 'name' === $this->order ) {
					$query->order( [ 'name' => 'asc' ] );
				} elseif ( 'random' === $this->order ) {
					$query->order( 'random' );
				} else {
					$query->order( [ 'registered_date' => 'desc' ] );
				}
			}

			// Get cached IDs.
			$vendor_ids = null;

			if ( 'random' !== $this->order ) {
				$vendor_ids = hivepress()->cache->get_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'models/vendor' );

				if ( is_array( $vendor_ids ) ) {
					$query = Models\Vendor::query()->filter(
						[
							'status' => 'publish',
							'id__in' => $vendor_ids,
						]
					)->order( 'id__in' )->limit( count( $vendor_ids ) );
				}
			}

			// Query vendors.
			$regular_query = new \WP_Query( $query->get_args() );

			// Cache IDs.
			if ( 'random' !== $this->order && is_null( $vendor_ids ) && $regular_query->post_count <= 1000 ) {
				hivepress()->cache->set_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'models/vendor', wp_list_pluck( $regular_query->posts, 'ID' ) );
			}
		}

		if ( $regular_query->have_posts() ) {
			$output .= '<div class="hp-vendors hp-grid hp-block">';
			$output .= '<div class="hp-row">';

			// Render vendors.
			while ( $regular_query->have_posts() ) {
				$regular_query->the_post();

				// Get vendor.
				$vendor = Models\Vendor::query()->get_by_id( get_post() );

				if ( $vendor ) {
					$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

					// Render vendor.
					$output .= ( new Template(
						[
							'template' => 'vendor_view_block',

							'context'  => [
								'vendor' => $vendor,
							],
						]
					) )->render();

					$output .= '</div>';
				}
			}

			$output .= '</div>';
			$output .= '</div>';
		}

		// Reset query.
		wp_reset_postdata();

		return $output;
	}
}
