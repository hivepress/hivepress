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
class Listing_Search_Results extends Container {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( have_posts() ) {
			$output .= parent::render();
		} else {
			$output .= 'todo no listings';
		}

		return $output;
	}
}
