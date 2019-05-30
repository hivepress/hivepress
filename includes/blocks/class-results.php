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
			$output .= ( new Element( [ 'filepath' => 'no-results' ] ) )->render();
		}

		return $output;
	}
}
