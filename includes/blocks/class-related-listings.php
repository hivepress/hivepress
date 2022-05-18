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
	 * @param array $meta Class meta values.
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
		$query = Models\Listing::query()->filter(
			[
				'status' => 'publish',
			]
		)->order( 'random' )
		->limit( $this->number );

		// Get listing.
		$listing = $this->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {

			// Exclude listing.
			$query->filter( [ 'id__not_in' => [ $listing->get_id() ] ] );

			// Set categories.
			if ( $listing->get_categories__id() ) {
				$query->filter( [ 'categories__in' => $listing->get_categories__id() ] );
			}

			/**
			 * Fires when related models are being queried. The dynamic part of the hook refers to the model name (e.g. `listing`).
			 *
			 * @hook hivepress/v1/models/{model_name}/relate
			 * @param {object} $query Related query.
			 * @param {object} $object Model object.
			 */
			do_action( 'hivepress/v1/models/listing/relate', $query, $listing );
		}

		// Set context.
		$this->context['listing_query'] = $query;

		parent::boot();
	}
}
