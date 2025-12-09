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
 * Renders vendors.
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
	 * Vendor category ID.
	 *
	 * @var int
	 */
	protected $category;

	/**
	 * Vendors order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Show verified only?
	 *
	 * @var bool
	 */
	protected $verified;

	/**
	 * HTML attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => hivepress()->translator->get_string( 'vendors' ),

				'settings' => [
					'columns'  => [
						'label'    => hivepress()->translator->get_string( 'columns_number' ),
						'type'     => 'select',
						'default'  => 3,
						'required' => true,
						'_order'   => 10,

						'options'  => [
							1 => '1',
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],

					'number'   => [
						'label'     => hivepress()->translator->get_string( 'items_number' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'required'  => true,
						'_order'    => 20,
					],

					'category' => [
						'label'       => hivepress()->translator->get_string( 'category' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_vendor_category' ],
						'_order'      => 30,
					],

					'order'    => [
						'label'    => hivepress()->translator->get_string( 'sort_order' ),
						'type'     => 'select',
						'required' => true,
						'_order'   => 200,

						'options'  => [
							'registered_date' => hivepress()->translator->get_string( 'by_date_registered' ),
							'name'            => hivepress()->translator->get_string( 'by_name' ),
							'random'          => hivepress()->translator->get_string( 'by_random' ),
						],
					],

					'verified' => [
						'label'  => hivepress()->translator->get_string( 'display_only_verified_vendors' ),
						'type'   => 'checkbox',
						'_order' => 210,
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'number' => get_option( 'hp_vendors_per_page' ),
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-vendors', 'hp-block', 'hp-grid' ],
			]
		);

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		global $wp_query;

		$output = '';

		if ( $this->number ) {

			// Get column width.
			$column_width = hp\get_column_width( $this->columns );

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
					)->limit( $this->number )
					->set_args(
						hivepress()->attribute->get_query_args(
							'vendor',
							array_diff_key( $this->get_args(), get_object_vars( $this ) )
						)
					);

					// Set category.
					if ( $this->category ) {
						$query->filter( [ 'categories__in' => $this->category ] );
					}

					// Set order.
					if ( 'name' === $this->order ) {
						$query->order( [ 'name' => 'asc' ] );
					} elseif ( 'random' === $this->order ) {
						$query->order( 'random' );
					} else {
						$query->order( [ 'registered_date' => 'desc' ] );
					}

					// Set verified flag.
					if ( $this->verified ) {
						$query->filter( [ 'verified' => true ] );
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
						)->order( 'id__in' )
						->limit( count( $vendor_ids ) );
					}
				}

				// Query vendors.
				$regular_query = new \WP_Query( $query->get_args() );

				// Cache IDs.
				if ( 'random' !== $this->order && is_null( $vendor_ids ) && $regular_query->post_count <= 1000 ) {
					hivepress()->cache->set_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'models/vendor', wp_list_pluck( $regular_query->posts, 'ID' ) );
				}
			} elseif ( ! $regular_query->have_posts() ) {
				$output = ( new Part( [ 'path' => 'page/no-results-message' ] ) )->render();
			}

			if ( $regular_query->have_posts() ) {
				$output .= '<div ' . hp\html_attributes( $this->attributes ) . '>';
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
		}

		return $output;
	}
}
