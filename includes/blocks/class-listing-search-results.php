<?php
/**
 * Listing search results block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing search results block class.
 *
 * @class Listing_Search_Results
 */
class Listing_Search_Results extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<div class="todo">todo' . wp_rand( 1, 9 ) . '</div>';
	}
}
