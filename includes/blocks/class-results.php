<?php
/**
 * Results block.
 *
 * @package HivePress\Blocks
 */
// todo.
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

		if ( have_posts() ) {
			$output .= parent::render();
		} else {
			$output .= ( new Part( [ 'path' => 'page/no-results' ] ) )->render();
		}

		return $output;
	}
}
