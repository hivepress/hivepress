<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages listings.
 *
 * @class Listing
 */
class Listing extends Entity {

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Render editor blocks.
		add_filter( 'hivepress/editor/block_html/listing_search', [ $this, 'render_search_block' ], 10, 2 );
		add_filter( 'hivepress/editor/block_html/listings', [ $this, 'render_listings_block' ], 10, 2 );
		add_filter( 'hivepress/editor/block_html/listing_categories', [ $this, 'render_categories_block' ], 10, 2 );
	}

	/**
	 * Renders search block.
	 *
	 * @param string $output
	 * @param array  $atts
	 * @return string
	 */
	public function render_search_block( $output, $atts ) {
		return hivepress()->template->render_part( 'listing/parts/search-form' );
	}

	/**
	 * Renders listings block.
	 *
	 * @param string $output
	 * @param array  $atts
	 * @return string
	 */
	public function render_listings_block( $output, $atts ) {
		$atts = shortcode_atts(
			[
				'category' => '',
				'number'   => 3,
				'columns'  => 3,
				'order'    => '',
				'status'   => '',
			],
			$atts
		);

		// Get column width.
		$columns      = absint( $atts['columns'] );
		$column_width = 12;

		if ( $columns > 0 && $columns <= 12 ) {
			$column_width = round( $column_width / $columns );
		}

		// Set query arguments.
		$query_args = [
			'post_type'      => hp_prefix( $this->name ),
			'post_status'    => 'publish',
			'posts_per_page' => absint( $atts['number'] ),
		];

		// Get category.
		if ( '' !== $atts['category'] ) {
			$query_args['tax_query'][] = [
				'taxonomy' => hp_prefix( $this->name . '_category' ),
				'terms'    => [ absint( $atts['category'] ) ],
			];
		}

		// Get order.
		if ( 'title' === $atts['order'] ) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'ASC';
		} elseif ( 'random' === $atts['order'] ) {
			$query_args['orderby'] = 'rand';
		}

		// Get status.
		if ( 'featured' === $atts['status'] ) {
			$query_args['meta_key']   = hp_prefix( $atts['status'] );
			$query_args['meta_value'] = '1';
		}

		// Query listings.
		$query = new \WP_Query( $query_args );

		$output = hivepress()->template->render_part(
			'listing/parts/loop-archive',
			[
				'listing_query' => $query,
				'column_width'  => $column_width,
			]
		);

		wp_reset_postdata();

		return $output;
	}

	/**
	 * Renders categories block.
	 *
	 * @param string $output
	 * @param array  $atts
	 * @return string
	 */
	public function render_categories_block( $output, $atts ) {
		$atts = shortcode_atts(
			[
				'parent'  => '',
				'number'  => 3,
				'columns' => 3,
				'order'   => '',
			],
			$atts
		);

		// Get column width.
		$columns      = absint( $atts['columns'] );
		$column_width = 12;

		if ( $columns > 0 && $columns <= 12 ) {
			$column_width = round( $column_width / $columns );
		}

		// Set category arguments.
		$category_args = [
			'taxonomy'   => hp_prefix( $this->name . '_category' ),
			'hide_empty' => false,
			'number'     => absint( $atts['number'] ),
			'parent'     => absint( $atts['parent'] ),
		];

		// Get order.
		if ( 'name' === $atts['order'] ) {
			$category_args['orderby'] = 'name';
		} elseif ( 'count' === $atts['order'] ) {
			$category_args['orderby'] = 'count';
			$category_args['order']   = 'DESC';
		} else {
			$category_args['orderby']  = 'meta_value_num';
			$category_args['order']    = 'ASC';
			$category_args['meta_key'] = 'hp_order';
		}

		// Get categories.
		$categories = get_terms( $category_args );

		// Set category count.
		foreach ( $categories as $category_index => $category ) {
			$categories[ $category_index ]->count = $this->get_category_count( $category->term_id );
		}

		$output = hivepress()->template->render_part(
			'category/parts/loop-archive',
			[
				'categories'   => $categories,
				'column_width' => $column_width,
			]
		);

		return $output;
	}
}
