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
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get category ID.
		$category_id = absint( $this->get_attribute( 'id' ) );

		if ( 0 !== $category_id ) {

			// Get category.
			$category = \HivePress\Models\Listing_Category::get( $category_id );

			if ( ! is_null( $category ) ) {
				$this->attributes['category'] = $category;

				// Render category.
				$output = parent::render();
			}
		}

		return $output;
	}
}
