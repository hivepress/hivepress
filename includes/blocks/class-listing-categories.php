<?php
/**
 * Listing categories block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders listing categories.
 */
class Listing_Categories extends Block {

	/**
	 * Template mode.
	 *
	 * @var string
	 */
	protected $mode = 'view';

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Listing categories number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Listing category parent ID.
	 *
	 * @var int
	 */
	protected $parent;

	/**
	 * Listing categories order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => hivepress()->translator->get_string( 'listing_categories' ),

				'settings' => [
					'columns' => [
						'label'    => hivepress()->translator->get_string( 'columns_number' ),
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
						'label'     => hivepress()->translator->get_string( 'items_number' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'required'  => true,
						'_order'    => 20,
					],

					'parent'  => [
						'label'       => hivepress()->translator->get_string( 'parent_category' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'_order'      => 30,
					],

					'order'   => [
						'label'    => hivepress()->translator->get_string( 'sort_order' ),
						'type'     => 'select',
						'required' => true,
						'_order'   => 40,

						'options'  => [
							'sort_order' => '&mdash;',
							'name'       => hivepress()->translator->get_string( 'by_name' ),
							'item_count' => hivepress()->translator->get_string( 'by_items_number' ),
						],
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set category parent.
		if ( empty( $this->parent ) ) {
			$listing_category = $this->get_context( 'listing_category' );

			if ( hp\is_class_instance( $listing_category, '\HivePress\Models\Listing_Category' ) ) {
				$this->parent = $listing_category->get_id();
			}
		}

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get column width.
		$column_width = hp\get_column_width( $this->columns );

		// Set query.
		$query = Models\Listing_Category::query()->limit( $this->number );

		// Set parent.
		$query->filter( [ 'parent' => absint( $this->parent ) ] );

		// Set order.
		if ( 'name' === $this->order ) {
			$query->order( [ 'name' => 'asc' ] );
		} elseif ( 'item_count' === $this->order ) {
			$query->order( [ 'item_count' => 'desc' ] );
		} else {
			$query->order( [ 'sort_order' => 'asc' ] );
		}

		// Get cached IDs.
		$listing_category_ids = hivepress()->cache->get_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'models/listing_category' );

		if ( is_array( $listing_category_ids ) ) {
			$query = Models\Listing_Category::query()->filter(
				[
					'id__in' => $listing_category_ids,
				]
			)->order( 'id__in' )
			->limit( count( $listing_category_ids ) );
		}

		// Query categories.
		if ( is_null( $listing_category_ids ) && ( ! $this->order || 'sort_order' === $this->order ) && ! Models\Listing_Category::query()->set_args( $query->get_args() )->filter(
			[
				'sort_order__gt' => 0,
			]
		)->get_first_id() ) {
			$categories = Models\Listing_Category::query()->set_args( $query->get_args() )->order( [ 'name' => 'asc' ] )->get();
		} else {
			$categories = $query->get();
		}

		// Cache IDs.
		if ( is_null( $listing_category_ids ) && $categories->count() <= 1000 ) {
			hivepress()->cache->set_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'models/listing_category', $categories->get_ids() );
		}

		// Render categories.
		if ( $categories->count() ) {
			$output  = '<div class="hp-listing-categories hp-grid hp-block">';
			$output .= '<div class="hp-row">';

			foreach ( $categories as $category ) {

				// Get category URL.
				$category_url = null;

				if ( 'submit' === $this->mode ) {
					$category_url = hivepress()->router->get_url( 'listing_submit_category_page', [ 'listing_category_id' => $category->get_id() ] );
				} else {
					if ( is_search() ) {
						$category_url = add_query_arg( [ '_category' => $category->get_id() ], hivepress()->router->get_current_url() );
					} else {
						$category_url = hivepress()->router->get_url( 'listing_category_view_page', [ 'listing_category_id' => $category->get_id() ] );
					}
				}

				// Render category.
				$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

				$output .= ( new Template(
					[
						'template' => 'listing_category_view_block',

						'context'  => [
							'listing_category'     => $category,
							'listing_category_url' => $category_url,
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
