<?php
/**
 * Listing categories block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing categories block class.
 *
 * @class Listing_Categories
 */
class Listing_Categories extends Block {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {

		// Set title.
		$args['title'] = esc_html__( 'Listing Categories', 'hivepress' );

		parent::__construct( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = 'todo';

		return $output;
	}
}
