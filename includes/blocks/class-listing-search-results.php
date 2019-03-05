<?php
/**
 * Listing search results block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

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
		return '<div class="todo hp-block">todo' . $this->get_attribute( 's' ) . '</div>';
	}
}
