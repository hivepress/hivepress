<?php
/**
 * Listing category block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing category block class.
 *
 * @class Listing_Category
 */
class Listing_Category extends Template {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var string
	 */
	protected static $settings = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		return '';
	}
}
