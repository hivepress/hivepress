<?php
/**
 * Listing block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing block class.
 *
 * @class Listing
 */
class Listing extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {

		// todo.
		$output = ( new Element( [ 'attributes' => [ 'path' => 'todo' ] ] ) )->render();

		return $output;
	}
}
