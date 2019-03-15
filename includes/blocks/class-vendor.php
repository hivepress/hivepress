<?php
/**
 * Vendor block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor block class.
 *
 * @class Vendor
 */
class Vendor extends Template {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$this->set_vendor( \HivePress\Models\Vendor::get( 265 ) );

		global $post;
		$post = get_post( 265 );
		setup_postdata( $post );

		$output = parent::render();

		wp_reset_postdata();

		return $output;
	}
}
