<?php
/**
 * Results block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Results block class.
 *
 * @class Results
 */
class Results extends Container {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( have_posts() || hivepress()->request->get_context( 'featured_ids' ) ) {
			$output .= parent::render();
		} elseif ( ! $this->optional ) {
			$output .= ( new Part( [ 'path' => 'page/no-results-message' ] ) )->render();
		}

		return $output;
	}
}
