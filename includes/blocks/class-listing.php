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
	 * Listing ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get listing ID.
		$listing_id = absint( $this->id );

		if ( 0 === $listing_id ) {
			$listing_id = absint( get_the_ID() );
		}

		if ( 0 !== $listing_id ) {

			// Get listing.
			$listing = \HivePress\Models\Listing::get( $listing_id );

			if ( ! is_null( $listing ) ) {
				$this->values['listing'] = $listing;

				// Render listing.
				$output = parent::render();
			}
		}

		return $output;
	}
}
