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

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$this->attributes['listing'] = \HivePress\Models\Listing::get( get_the_ID() );

		return parent::render();
	}
}
