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
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'title' => esc_html__( 'Listing Categories', 'hivepress' ),
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
	}
}
