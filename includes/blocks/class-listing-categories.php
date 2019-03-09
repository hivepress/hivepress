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
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title' => esc_html__( 'Listing Categories', 'hivepress' ),
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		return '';
	}
}
