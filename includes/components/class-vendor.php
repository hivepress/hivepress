<?php
/**
 * Vendor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor component class.
 *
 * @class Vendor
 */
final class Vendor {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Update vendor.
		add_action( 'added_user_meta', [ $this, 'update_vendor' ], 10, 4 );
		add_action( 'updated_user_meta', [ $this, 'update_vendor' ], 10, 4 );

		// Update image.
		add_action( 'added_post_meta', [ $this, 'update_image' ], 10, 4 );

		// Import vendors.
		add_action( 'import_start', [ $this, 'import_vendors' ] );

		if ( ! is_admin() ) {

			// Disable redirect.
			add_action( 'template_redirect', [ $this, 'disable_page_redirect' ], 1 );

			// Set page title.
			add_filter( 'hivepress/v1/controllers/vendor/routes/view_vendor', [ $this, 'set_page_title' ] );
		}
	}

	/**
	 * Updates vendor.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $user_id User ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_vendor( $meta_id, $user_id, $meta_key, $meta_value ) {
		if ( in_array( $meta_key, [ 'first_name', 'description' ], true ) ) {

			// Get vendor ID.
			$vendor_id = hp\get_post_id(
				[
					'post_type'   => 'hp_vendor',
					'post_status' => 'publish',
					'author'      => $user_id,
				]
			);

			if ( 0 !== $vendor_id ) {

				// Get user.
				$user = get_userdata( $user_id );

				if ( false !== $user ) {

					// Update vendor.
					wp_update_post(
						[
							'ID'           => $vendor_id,
							'post_title'   => $user->display_name,
							'post_content' => get_user_meta( $user_id, 'description', true ),
						]
					);
				}
			}
		}
	}

	/**
	 * Updates image.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $attachment_id Attachment ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_image( $meta_id, $attachment_id, $meta_key, $meta_value ) {
		if ( 'hp_parent_field' === $meta_key && 'image_id' === $meta_value ) {

			// Get attachment.
			$attachment = get_post( $attachment_id );

			if ( 'attachment' === $attachment->post_type && 0 === $attachment->post_parent ) {

				// Get vendor ID.
				$vendor_id = hp\get_post_id(
					[
						'post_type'   => 'hp_vendor',
						'post_status' => 'publish',
						'author'      => $attachment->post_author,
					]
				);

				if ( 0 !== $vendor_id ) {

					// Update image.
					set_post_thumbnail( $vendor_id, $attachment_id );
				}
			}
		}
	}

	/**
	 * Imports vendors.
	 */
	public function import_vendors() {
		remove_action( 'added_post_meta', [ $this, 'update_image' ] );
	}

	/**
	 * Disables page redirect.
	 */
	public function disable_page_redirect() {
		if ( is_singular( 'hp_vendor' ) ) {
			remove_action( 'template_redirect', 'redirect_canonical' );
		}
	}

	/**
	 * Sets page title.
	 *
	 * @param array $route Route arguments.
	 * @return array
	 */
	public function set_page_title( $route ) {
		return array_merge( $route, [ 'title' => sprintf( hivepress()->translator->get_string( 'listings_by_vendor' ), get_the_title() ) ] );
	}
}
