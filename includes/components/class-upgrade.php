<?php
/**
 * Upgrade component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles the database upgrades.
 */
final class Upgrade extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Upgrade terms.
		add_action( 'hivepress/v1/update', [ $this, 'upgrade_terms' ], 10 );

		// Upgrade comments.
		add_action( 'hivepress/v1/update', [ $this, 'upgrade_comments' ], 20 );

		// Upgrade events.
		add_action( 'hivepress/v1/update', [ $this, 'upgrade_events' ], 30 );

		// Upgrade database.
		add_action( 'hivepress/v1/update', [ $this, 'upgrade_database' ], 40 );

		// Upgrade attributes.
		add_action( 'hivepress/v1/update', [ $this, 'upgrade_attributes' ], 50 );

		// Upgrade emails.
		add_action( 'hivepress/v1/activate', [ $this, 'upgrade_emails' ] );

		parent::__construct( $args );
	}

	/**
	 * Upgrades terms.
	 *
	 * @param string $version Old version.
	 * @deprecated Since version 1.3.0
	 */
	public function upgrade_terms( $version ) {
		if ( version_compare( $version, '1.3.0', '<' ) ) {

			// Get term IDs.
			$term_ids = get_terms(
				[
					'taxonomy'   => 'hp_listing_category',
					'hide_empty' => false,
					'fields'     => 'ids',
				]
			);

			// Upgrade terms.
			foreach ( $term_ids as $term_id ) {
				if ( get_term_meta( $term_id, 'hp_order', true ) === '' ) {
					update_term_meta( $term_id, 'hp_order', '0' );
				}
			}
		}
	}

	/**
	 * Upgrades comments.
	 *
	 * @param string $version Old version.
	 * @deprecated Since version 1.3.0
	 */
	public function upgrade_comments( $version ) {
		if ( version_compare( $version, '1.3.0', '<' ) ) {

			// Get comments.
			$comments = get_comments(
				[
					'type' => 'hp_listing_package',
				]
			);

			// Upgrade comments.
			foreach ( $comments as $comment ) {
				if ( $comment->comment_post_ID ) {
					update_comment_meta( $comment->comment_ID, 'hp_expire_period', get_post_meta( $comment->comment_post_ID, 'hp_expiration_period', true ) );
					update_comment_meta( $comment->comment_ID, 'hp_featured', get_post_meta( $comment->comment_post_ID, 'hp_featured', true ) );
				}

				if ( $comment->comment_approved ) {
					update_comment_meta( $comment->comment_ID, 'hp_default', '1' );
				} else {
					wp_update_comment(
						[
							'comment_ID'       => $comment->comment_ID,
							'comment_approved' => 1,
						]
					);
				}
			}

			// Get comments.
			$comments = get_comments(
				[
					'type' => 'hp_review',
				]
			);

			// Upgrade comments.
			foreach ( $comments as $comment ) {
				wp_update_comment(
					[
						'comment_ID'    => $comment->comment_ID,
						'comment_karma' => absint( get_comment_meta( $comment->comment_ID, 'hp_rating', true ) ),
					]
				);

				delete_comment_meta( $comment->comment_ID, 'hp_rating' );
			}
		}
	}

	/**
	 * Upgrades events.
	 *
	 * @param string $version Old version.
	 * @deprecated Since version 1.3.0
	 */
	public function upgrade_events( $version ) {
		if ( version_compare( $version, '1.3.0', '<' ) ) {

			// Unchedule events.
			$periods = [ 'hourly', 'twicedaily', 'daily' ];

			foreach ( $periods as $period ) {
				$timestamp = wp_next_scheduled( 'hivepress/v1/cron/' . $period );

				if ( $timestamp ) {
					wp_unschedule_event( $timestamp, 'hivepress/v1/cron/' . $period );
				}
			}
		}
	}

	/**
	 * Upgrades database.
	 *
	 * @param string $version Old version.
	 * @deprecated Since version 1.3.0
	 */
	public function upgrade_database( $version ) {
		global $wpdb;

		if ( version_compare( $version, '1.3.0', '<' ) ) {

			// Upgrade user meta.
			$wpdb->update( $wpdb->usermeta, [ 'meta_key' => 'hp_image' ], [ 'meta_key' => 'hp_image_id' ] );

			// Upgrade post meta.
			$wpdb->update( $wpdb->postmeta, [ 'meta_key' => 'hp_submit_limit' ], [ 'meta_key' => 'hp_submission_limit' ] );
			$wpdb->update( $wpdb->postmeta, [ 'meta_key' => 'hp_expire_period' ], [ 'meta_key' => 'hp_expiration_period' ] );

			// Upgrade term meta.
			$wpdb->update( $wpdb->termmeta, [ 'meta_key' => 'hp_image' ], [ 'meta_key' => 'hp_image_id' ] );
			$wpdb->update( $wpdb->termmeta, [ 'meta_key' => 'hp_sort_order' ], [ 'meta_key' => 'hp_order' ] );

			// Upgrade options.
			$wpdb->update( $wpdb->options, [ 'option_name' => 'hp_email_user_password_request' ], [ 'option_name' => 'hp_email_user_request_password' ] );
		}
	}

	/**
	 * Upgrades attributes.
	 *
	 * @param string $version Old version.
	 * @deprecated Since version 1.3.13
	 */
	public function upgrade_attributes( $version ) {
		if ( version_compare( $version, '1.3.13', '<' ) ) {
			$attributes = get_posts(
				[
					'post_type'      => [ 'hp_listing_attribute', 'hp_vendor_attribute' ],
					'post_status'    => 'any',
					'posts_per_page' => -1,
				]
			);

			foreach ( $attributes as $attribute ) {
				$areas  = (array) $attribute->hp_display_areas;
				$format = (string) $attribute->hp_display_format;

				if ( array_intersect( [ 'view_block_secondary', 'view_page_secondary' ], $areas ) && strpos( $format, '%label%' ) === false ) {
					update_post_meta( $attribute->ID, 'hp_display_format', '%label%: ' . $format );

					wp_update_post(
						[
							'ID' => $attribute->ID,
						]
					);
				}
			}
		}
	}

	/**
	 * Upgrades emails.
	 *
	 * @deprecated Since version 1.5.0
	 */
	public function upgrade_emails() {

		// Get email classes.
		$email_classes = hivepress()->get_classes( 'emails' );

		foreach ( $email_classes as $email_class ) {
			if ( $email_class::get_meta( 'label' ) ) {

				// Get option value.
				$option_name  = 'hp_email_' . $email_class::get_meta( 'name' );
				$option_value = get_option( $option_name );

				if ( false !== $option_value ) {

					// Create email object.
					$email = hp\create_class_instance( $email_class );

					if ( $email ) {

						// Add email post.
						if ( wp_insert_post(
							[
								'post_title'   => $email->get_subject(),
								'post_name'    => $email_class::get_meta( 'name' ),
								'post_content' => $option_value,
								'post_type'    => 'hp_email',
								'post_status'  => 'publish',
							]
						) ) {

							// Delete option.
							delete_option( $option_name );
						}
					}
				}
			}
		}
	}
}
