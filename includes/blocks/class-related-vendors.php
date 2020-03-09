<?php
/**
 * Related vendors.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Related vendors block class.
 *
 * @class Related_Vendors
 */
class Related_Vendors extends Vendors {

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
				'number' => get_option( 'hp_vendors_related_per_page' ),
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
		$vendor_query = Models\Vendor::query()->filter(
			[
				'status' => 'publish',
			]
		)->order( 'random' )
		->limit( $this->number );

		// Get listing.
		$listing = $this->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {

			// Set vendor ID.
			$vendor_query->filter(
				[
					'id__in' => [ $listing->get_vendor__id() ],
				]
			)->limit( 1 );
		} else {

			// Get vendor.
			$vendor = $this->get_context( 'vendor' );

			if ( hp\is_class_instance( $vendor, '\HivePress\Models\Vendor' ) ) {

				// Exclude vendor.
				$vendor_query->filter( [ 'id__not_in' => [ $vendor->get_id() ] ] );

				// Set categories.
				if ( $vendor->get_categories__id() ) {
					$vendor_query->filter( [ 'categories__in' => $vendor->get_categories__id() ] );
				}
			}
		}

		// Set context.
		$this->context['vendor_query'] = $vendor_query;

		parent::boot();
	}
}
