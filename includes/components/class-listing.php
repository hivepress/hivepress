<?php
/**
 * Listing component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing component class.
 *
 * @class Listing
 */
final class Listing {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Set vendor.
		add_action( 'save_post_' . hp\prefix( 'listing' ), [ $this, 'set_vendor' ], 10, 2 );
	}

	/**
	 * Sets vendor.
	 *
	 * @param int     $listing_id Listing ID.
	 * @param WP_Post $listing Listing object.
	 */
	public function set_vendor( $listing_id, $listing ) {

		// Get vendor ID.
		$vendor_id = hp\get_post_id(
			[
				'post_type'   => hp\prefix( 'vendor' ),
				'post_status' => 'any',
				'post__in'    => [ absint( $listing->post_parent ) ],
			]
		);

		if ( 0 === $vendor_id ) {

			// Get user ID.
			$user_id = absint( $listing->post_author );

			// Get vendor ID.
			$vendor_id = hp\get_post_id(
				[
					'post_type'   => hp\prefix( 'vendor' ),
					'post_status' => 'any',
					'author'      => $user_id,
				]
			);

			if ( 0 === $vendor_id ) {

				// Add vendor.
				$vendor_id = wp_insert_post(
					[
						'post_title'   => get_userdata( $user_id )->display_name,
						'post_content' => get_user_meta( $user_id, 'description', true ),
						'post_type'    => hp\prefix( 'vendor' ),
						'post_status'  => 'publish',
						'post_author'  => $user_id,
					]
				);
			}

			// Set vendor ID.
			wp_update_post(
				[
					'ID'          => $listing_id,
					'post_parent' => $vendor_id,
				]
			);
		}
	}
}
