<?php
/**
 * Listing block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing block class.
 *
 * @class Listing
 */
class Listing extends Template {

	// todo.
	public function render() {
		$this->attributes['listing'] = \HivePress\Models\Listing::get( get_the_ID() );

		return parent::render();
	}
}
