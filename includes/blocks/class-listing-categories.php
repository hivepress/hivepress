<?php
/**
 * Listing categories block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders listing categories.
 */
class Listing_Categories extends Categories {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => hivepress()->translator->get_string( 'listing_categories' ),

				'settings' => [
					'parent' => [
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set category parent.
		if ( empty( $this->parent ) ) {
			$listing_category = $this->get_context( 'listing_category' );

			if ( hp\is_class_instance( $listing_category, '\HivePress\Models\Listing_Category' ) ) {
				$this->parent = $listing_category->get_id();
			}
		}

		// Set category parent name.
		$this->parent_category = 'listing';

		parent::boot();
	}
}
