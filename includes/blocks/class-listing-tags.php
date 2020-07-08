<?php
/**
 * Listing tags block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing tags block class.
 *
 * @class Listing_Tags
 */
class Listing_Tags extends Block {

	/**
	 * Listing tags number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Listing tags order.
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
				'label'    => hivepress()->translator->get_string( 'listing_tags' ),

				'settings' => [
					'number' => [
						'label'     => hivepress()->translator->get_string( 'items_number' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 10,
						'required'  => true,
						'_order'    => 10,
					],

					'order'  => [
						'label'    => hivepress()->translator->get_string( 'sort_order' ),
						'type'     => 'select',
						'required' => true,
						'_order'   => 20,

						'options'  => [
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
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Set arguments.
		$args = [
			'taxonomy'   => 'hp_listing_tag',
			'hide_empty' => false,
		];

		if ( $this->number ) {
			$args['number'] = absint( $this->number );
		}

		if ( 'item_count' === $this->order ) {
			$args['orderby'] = 'count';
			$args['order']   = 'DESC';
		}

		// Get tags.
		$tags = get_terms( $args );

		if ( $tags ) {

			// Render tags.
			$output .= '<div class="hp-listing-tags hp-block tagcloud">';

			foreach ( $tags as $tag ) {
				$output .= '<a href="' . esc_url( get_term_link( $tag ) ) . '" class="hp-listing-tag tag-cloud-link">' . esc_html( $tag->name ) . '</a>';
			}

			$output .= '</div>';
		}

		return $output;
	}
}
