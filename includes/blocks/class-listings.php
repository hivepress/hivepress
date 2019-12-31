<?php
/**
 * Listings block.
 *
 * @package HivePress\Blocks
 */
// todo.
namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings block class.
 *
 * @class Listings
 */
class Listings extends Block {

	/**
	 * Template type.
	 *
	 * @var string
	 */
	protected $template = 'view';

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Listings number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Listings category.
	 *
	 * @var int
	 */
	protected $category;

	/**
	 * Listings order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Featured flag.
	 *
	 * @var bool
	 */
	protected $featured;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'title'    => hivepress()->translator->get_string( 'listings' ),

				'settings' => [
					'columns'  => [
						'label'    => esc_html__( 'Columns', 'hivepress' ),
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

					'number'   => [
						'label'     => esc_html__( 'Number', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'_order'    => 20,
					],

					'category' => [
						'label'       => esc_html__( 'Category', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'default'     => '',
						'_order'      => 30,
					],

					'order'    => [
						'label'   => esc_html__( 'Order', 'hivepress' ),
						'type'    => 'select',
						'_order'  => 40,

						'options' => [
							''       => esc_html__( 'Date', 'hivepress' ),
							'title'  => esc_html__( 'Title', 'hivepress' ),
							'random' => esc_html_x( 'Random', 'sorting order', 'hivepress' ),
						],
					],

					'featured' => [
						'label'  => hivepress()->translator->get_string( 'display_only_featured_listings' ),
						'type'   => 'checkbox',
						'_order' => 50,
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

		// Get listing queries.
		$regular_query  = $wp_query;
		$featured_query = null;

		if ( is_single() || ( hp\get_array_value( $regular_query->query_vars, 'post_type' ) !== 'hp_listing' && ! is_tax( 'hp_listing_category' ) ) ) {

			// Set query.
			$query = Models\Listing::query()->filter( [ 'status' => 'publish' ] )->limit( $this->number );

			// Set category.
			if ( $this->category ) {
				$query->filter( [ 'category_ids' => $this->category ] );
			}

			// Set order.
			$query->order( [ 'date_created' => 'desc' ] );

			if ( 'title' === $this->order ) {
				$query->order( [ 'title' => 'asc' ] );
			} elseif ( 'random' === $this->order ) {
				$query->order( 'random' );
			}

			// Get featured.
			if ( $this->featured ) {
				$query->filter( [ 'featured' => true ] );
			}

			// Get cached IDs.
			$listing_ids = null;

			if ( 'random' !== $this->order ) {
				$listing_ids = hivepress()->cache->get_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'listing' );

				if ( is_array( $listing_ids ) ) {
					$query = Models\Listing::query()->filter(
						[
							'status' => 'publish',
							'id__in' => $listing_ids,
						]
					)->order( 'id__in' )->limit( count( $listing_ids ) );
				}
			}

			// Query listings.
			$regular_query = new \WP_Query( $query->get_args() );

			// Cache IDs.
			if ( 'random' !== $this->order && is_null( $listing_ids ) && $regular_query->post_count <= 1000 ) {
				hivepress()->cache->set_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'listing', wp_list_pluck( $regular_query->posts, 'ID' ) );
			}
		} elseif ( 'edit' !== $this->template && get_query_var( 'hp_featured_ids' ) ) {

			// Query featured listings.
			$featured_query = new \WP_Query(
				Models\Listing::query()->filter(
					[
						'status' => 'publish',
						'id__in' => array_map( 'absint', (array) get_query_var( 'hp_featured_ids' ) ),
					]
				)->order( 'random' )->limit( get_option( 'hp_listings_featured_per_page' ) )->get_args()
			);
		}

		if ( $regular_query->have_posts() ) {
			if ( 'edit' === $this->template ) {
				$output .= '<table class="hp-table">';
			} else {
				$output .= '<div class="hp-grid hp-block">';
				$output .= '<div class="hp-row">';
			}

			// Render featured listings.
			if ( ! is_null( $featured_query ) ) {
				while ( $featured_query->have_posts() ) {
					$featured_query->the_post();

					// Get listing.
					$listing = Models\Listing::query()->get_by_id( get_post() );

					if ( ! is_null( $listing ) ) {
						$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

						// Render listing.
						$output .= ( new Listing(
							[
								'template' => $this->template,

								'context'  => [
									'listing' => $listing,
								],
							]
						) )->render();

						$output .= '</div>';
					}
				}
			}

			// Render regular listings.
			while ( $regular_query->have_posts() ) {
				$regular_query->the_post();

				// Get listing.
				$listing = Models\Listing::query()->get_by_id( get_post() );

				if ( ! is_null( $listing ) ) {
					if ( 'edit' !== $this->template ) {
						$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';
					}

					// Render listing.
					$output .= ( new Listing(
						[
							'template' => $this->template,

							'context'  => [
								'listing' => $listing,
							],
						]
					) )->render();

					if ( 'edit' !== $this->template ) {
						$output .= '</div>';
					}
				}
			}

			if ( 'edit' === $this->template ) {
				$output .= '</table>';
			} else {
				$output .= '</div>';
				$output .= '</div>';
			}
		}

		// Reset query.
		wp_reset_postdata();

		return $output;
	}
}
