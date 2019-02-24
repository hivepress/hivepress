<?php
/**
 * Listings block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

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
		parent::__construct( $args );

		// Set title.
		$this->set_title( esc_html__( 'Listings', 'hivepress' ) );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$output = '<div class="hp-listings"><div class="hp-row">';

		while ( have_posts() ) {
			the_post();

			$output .= '<div class="hp-col-sm-6 hp-col-xs-12">' . ( new Listing() )->render() . '</div>';
		}

		$output .= '</div></div>';

		return $output;
	}
}
