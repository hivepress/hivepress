<?php
/**
 * Related listings.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders related listings.
 */
class Related_Listings extends Listings {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => null,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'number' => get_option( 'hp_listings_related_per_page' ),
				'order'  => 'random',
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set query.
		$listing_query = Models\Listing::query()->filter(
			[
				'status' => 'publish',
			]
		)->order( 'random' )
		->limit( $this->number );

		// Get listing.
		$listing = $this->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {

			// Exclude listing.
			$listing_query->filter( [ 'id__not_in' => [ $listing->get_id() ] ] );

			// Set categories.
			if ( $listing->get_categories__id() ) {
				$listing_query->filter( [ 'categories__in' => $listing->get_categories__id() ] );
			}
		}

		// Set context.
		$this->context['listing_query'] = $listing_query;

		parent::boot();
	}
}
