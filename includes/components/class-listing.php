<?php
/**
 * Listing component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Emails;

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
		add_action( 'hivepress/v1/models/listing/update', [ $this, 'set_vendor' ] );

		// Set image.
		add_action( 'add_attachment', [ $this, 'set_image' ] );
		add_action( 'edit_attachment', [ $this, 'set_image' ] );

		// Update status.
		add_action( 'hivepress/v1/models/listing/update_status', [ $this, 'update_status' ], 10, 3 );

		// Expire listings.
		add_action( 'hivepress/v1/cron/hourly', [ $this, 'expire_listings' ] );

		// Import listings.
		add_action( 'import_start', [ $this, 'import_listings' ] );

		if ( is_admin() ) {

			// Add states.
			add_filter( 'display_post_states', [ $this, 'add_states' ], 10, 2 );
		} else {

			// Add menu items.
			add_filter( 'hivepress/v1/menus/account', [ $this, 'add_menu_items' ] );
		}
	}

	/**
	 * Sets vendor.
	 *
	 * @param int $listing_id Listing ID.
	 */
	public function set_vendor( $listing_id ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/listing/update', [ $this, 'set_vendor' ] );

		// Get listing.
		$listing = get_post( $listing_id );

		// Get user ID.
		$user_id = absint( $listing->post_author );

		// Get vendor ID.
		$vendor_id = hp\get_post_id(
			[
				'post_type'   => 'hp_vendor',
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
					'post_type'    => 'hp_vendor',
					'post_status'  => 'publish',
					'post_author'  => $user_id,
				]
			);

			if ( 0 !== $vendor_id ) {

				// Get image ID.
				$image_id = hp\get_post_id(
					[
						'post_type'   => 'attachment',
						'post_parent' => 0,
						'author'      => $user_id,
						'meta_key'    => 'hp_parent_field',
						'meta_value'  => 'image_id',
					]
				);

				if ( 0 !== $image_id ) {

					// Update image.
					set_post_thumbnail( $vendor_id, $image_id );
				}
			}
		}

		if ( 0 !== $vendor_id && ( 0 === $listing->post_parent || $listing->post_parent !== $vendor_id ) ) {

			// Set vendor ID.
			wp_update_post(
				[
					'ID'          => $listing_id,
					'post_parent' => $vendor_id,
				]
			);

			if ( 0 !== $listing->post_parent ) {

				// Get attachment IDs.
				$attachment_ids = get_posts(
					[
						'post_type'      => 'attachment',
						'post_status'    => 'any',
						'post_parent'    => $listing_id,
						'posts_per_page' => -1,
						'fields'         => 'ids',
					]
				);

				// Set vendor ID.
				foreach ( $attachment_ids as $attachment_id ) {
					wp_update_post(
						[
							'ID'          => $attachment_id,
							'post_author' => $user_id,
						]
					);
				}
			}
		}
	}

	/**
	 * Sets image.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	public function set_image( $attachment_id ) {

		// Get listing ID.
		$listing_id = wp_get_post_parent_id( $attachment_id );

		if ( get_post_type( $listing_id ) === 'hp_listing' ) {

			// Get mime type.
			$mime_type = get_post_mime_type( $attachment_id );

			if ( in_array( $mime_type, [ 'image/jpeg', 'image/png' ], true ) ) {

				// Get image IDs.
				$image_ids = wp_list_pluck( get_attached_media( 'image', $listing_id ), 'ID' );

				// Set image.
				if ( ! empty( $image_ids ) ) {
					set_post_thumbnail( $listing_id, reset( $image_ids ) );
				}
			}
		}
	}

	/**
	 * Updates status.
	 *
	 * @param int    $listing_id Listing ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function update_status( $listing_id, $new_status, $old_status ) {
		if ( 'pending' === $old_status ) {

			// Get listing.
			$listing = get_post( $listing_id );

			// Get user.
			$user = get_userdata( $listing->post_author );

			if ( 'publish' === $new_status ) {

				// Send approval email.
				( new Emails\Listing_Approve(
					[
						'recipient' => $user->user_email,
						'tokens'    => [
							'user_name'     => $user->display_name,
							'listing_title' => $listing->post_title,
							'listing_url'   => get_permalink( $listing_id ),
						],
					]
				) )->send();
			} elseif ( 'trash' === $new_status ) {

				// Send rejection email.
				( new Emails\Listing_Reject(
					[
						'recipient' => $user->user_email,
						'tokens'    => [
							'user_name'     => $user->display_name,
							'listing_title' => $listing->post_title,
						],
					]
				) )->send();
			}
		}

		if ( 'publish' === $new_status ) {

			// Get expiration period.
			$expiration_period = absint( get_option( 'hp_listing_expiration_period' ) );

			if ( $expiration_period > 0 && ! metadata_exists( 'post', $listing_id, 'hp_expiration_time' ) ) {

				// Set expiration time.
				update_post_meta( $listing_id, 'hp_expiration_time', time() + $expiration_period * DAY_IN_SECONDS );
			}
		}
	}

	/**
	 * Expires listings.
	 */
	public function expire_listings() {

		// Set query arguments.
		$query_args = [
			'post_type'      => 'hp_listing',
			'post_status'    => 'publish',
			'posts_per_page' => -1,

			'meta_query'     => [
				'time_clause' => [
					'value'   => time(),
					'compare' => '<=',
					'type'    => 'NUMERIC',
				],
			],
		];

		// Get expirable listings.
		$expirable_listings = get_posts(
			hp\merge_arrays(
				$query_args,
				[
					'meta_query' => [
						'time_clause' => [
							'key' => 'hp_expiration_time',
						],
					],
				]
			)
		);

		// Update expirable listings.
		foreach ( $expirable_listings as $listing ) {

			// Update status.
			wp_update_post(
				[
					'ID'          => $listing->ID,
					'post_status' => 'trash',
				]
			);

			// Delete timestamp.
			delete_post_meta( $listing->ID, 'hp_expiration_time' );

			// Send email.
			$user = get_userdata( $listing->post_author );

			if ( false !== $user ) {
				( new Emails\Listing_Expire(
					[
						'recipient' => $user->user_email,
						'tokens'    => [
							'user_name'     => $user->display_name,
							'listing_title' => $listing->post_title,
						],
					]
				) )->send();
			}
		}

		// Get featured listing IDs.
		$featured_listing_ids = get_posts(
			hp\merge_arrays(
				$query_args,
				[
					'fields'     => 'ids',

					'meta_query' => [
						'time_clause' => [
							'key' => 'hp_featuring_time',
						],
					],
				]
			)
		);

		// Update featured listings.
		foreach ( $featured_listing_ids as $listing_id ) {

			// Delete status.
			delete_post_meta( $listing_id, 'hp_featured' );

			// Delete timestamp.
			delete_post_meta( $listing_id, 'hp_featuring_time' );
		}
	}

	/**
	 * Imports listings.
	 */
	public function import_listings() {
		remove_action( 'add_attachment', [ $this, 'set_image' ] );
		remove_action( 'edit_attachment', [ $this, 'set_image' ] );
	}

	/**
	 * Adds states.
	 *
	 * @param array   $states Listing states.
	 * @param WP_Post $listing Listing object.
	 * @return array
	 */
	public function add_states( $states, $listing ) {
		if ( 'hp_listing' === $listing->post_type ) {
			if ( get_post_meta( $listing->ID, 'hp_featured', true ) ) {
				$states[] = esc_html_x( 'Featured', 'listing status', 'hivepress' );
			}

			if ( get_post_meta( $listing->ID, 'hp_verified', true ) ) {
				$states[] = esc_html_x( 'Verified', 'listing status', 'hivepress' );
			}
		}

		return $states;
	}

	/**
	 * Adds menu items.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function add_menu_items( $menu ) {
		if ( hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => [ 'draft', 'pending', 'publish' ],
				'author'      => get_current_user_id(),
			]
		) !== 0 ) {
			$menu['items']['edit_listings'] = [
				'route' => 'listing/edit_listings',
				'order' => 10,
			];
		}

		return $menu;
	}
}
