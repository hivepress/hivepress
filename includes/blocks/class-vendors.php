<?php
/**
 * Vendors block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendors block class.
 *
 * @class Vendors
 */
class Vendors extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Set query.
		$query = Models\Vendor::query()->filter( [ 'status' => 'publish' ] )->limit( 1 );

		// Set listing ID.
		$listing = $this->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {
			$query->filter( [ 'id__in' => [ $listing->get_vendor__id() ] ] );
		}

		// Query vendors.
		$regular_query = new \WP_Query( $query->get_args() );

		// Render vendors.
		if ( $regular_query->have_posts() ) {
			$output  = '<div class="hp-grid">';
			$output .= '<div class="hp-row">';

			while ( $regular_query->have_posts() ) {
				$regular_query->the_post();

				// Get vendor.
				$vendor = Models\Vendor::query()->get_by_id( get_post() );

				if ( $vendor ) {
					$output .= '<div class="hp-grid__item hp-col-xs-12">';

					// Render vendor.
					$output .= ( new Template(
						[
							'template' => 'vendor_view_block',

							'context'  => [
								'vendor' => $vendor,
							],
						]
					) )->render();

					$output .= '</div>';
				}
			}

			$output .= '</div>';
			$output .= '</div>';
		}

		// Reset query.
		wp_reset_postdata();

		return $output;
	}
}
