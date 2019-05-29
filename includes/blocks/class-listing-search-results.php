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
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

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
			$output .= ( new Element( [ 'file_path' => 'no-results' ] ) )->render();
		}

		return $output;
	}
}
