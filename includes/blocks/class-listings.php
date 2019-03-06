<?php
/**
 * Listings block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings block class.
 *
 * @class Listings
 */
class Listings extends Block {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'title' => esc_html__( 'Listings', 'hivepress' ),
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$query = new \WP_Query(
			[
				'post_type'      => 'hp_listing',
				'posts_per_page' => -1,
			]
		);

		$output = '<div class="hp-listings"><div class="hp-row">';

		while ( $query->have_posts() ) {
			$query->the_post();

			$output .= '<div class="hp-col-sm-6 hp-col-xs-12">' . ( new Listing( [ 'attributes' => [ 'template_name' => 'listing_view_summary' ] ] ) )->render() . '</div>';
		}

		$output .= '</div></div>';

		return $output;
	}
}
