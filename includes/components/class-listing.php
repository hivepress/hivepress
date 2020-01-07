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
final class Listing extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Update vendor.
		add_action( 'hivepress/v1/models/listing/update', [ $this, 'update_vendor' ] );

		// Update image.
		add_action( 'hivepress/v1/models/listing/update_images', [ $this, 'update_image' ] );

		// Update status.
		add_action( 'hivepress/v1/models/listing/update_status', [ $this, 'update_status' ], 10, 3 );

		// Expire listings.
		add_action( 'hivepress/v1/events/hourly', [ $this, 'expire_listings' ] );

		// Add submission fields.
		add_filter( 'hivepress/v1/forms/listing_submit', [ $this, 'add_submit_fields' ] );

		if ( is_admin() ) {

			// Add post states.
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

		} else {
			// Alter account menu.
			add_filter( 'hivepress/v1/menus/user_account', [ $this, 'alter_account_menu' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block/blocks', [ $this, 'alter_listing_view_blocks' ], 10, 2 );
		}

		parent::__construct( $args );
	}

	/**
	 * Updates listing vendor.
	 *
	 * @param int $listing_id Listing ID.
	 */
	public function update_vendor( $listing_id ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/listing/update', [ $this, 'update_vendor' ] );

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $listing_id );

		// Get user.
		$user = $listing->get_user();

		if ( empty( $user ) ) {
			return;
		}

		// Get vendor.
		$vendor = Models\Vendor::query()->filter( [ 'user' => $user->get_id() ] )->get_first();

		if ( empty( $vendor ) ) {

			// Add vendor.
			$vendor = ( new Models\Vendor() )->fill(
				[
					'name'        => $user->get_display_name(),
					'description' => $user->get_description(),
					'status'      => 'publish',
					'image'       => $user->get_image__id(),
					'user'        => $user->get_id(),
				]
			);

			if ( ! $vendor->save() ) {
				return;
			}
		}

		if ( $listing->get_vendor__id() !== $vendor->get_id() ) {

			// Update attachments.
			if ( $listing->get_vendor__id() ) {
				$attachments = Models\Attachment::query()->filter(
					[
						'parent' => $listing->get_id(),
						'model'  => 'listing',
					]
				)->get_all();

				foreach ( $attachments as $attachment ) {
					$attachment->set_user( $user->get_id() )->save();
				}
			}

			// Update listing.
			$listing->set_vendor( $vendor->get_id() )->save();
		}
	}

	/**
	 * Updates listing image.
	 *
	 * @param int $listing_id Listing ID.
	 */
	public function update_image( $listing_id ) {

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $listing_id );

		// Update image.
		$image_ids = $listing->get_images__id();

		if ( $image_ids ) {
			$listing->set_image( reset( $image_ids ) )->save();
		}
	}

	/**
	 * Updates listing status.
	 *
	 * @param int    $listing_id Listing ID.
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 */
	public function update_status( $listing_id, $new_status, $old_status ) {

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $listing_id );

		if ( 'pending' === $old_status ) {

			// Get user.
			$user = $listing->get_user();

			if ( $user ) {
				if ( 'publish' === $new_status ) {

					// Send approval email.
					( new Emails\Listing_Approve(
						[
							'recipient' => $user->get_email(),

							'tokens'    => [
								'user_name'     => $user->get_display_name(),
								'listing_title' => $listing->get_title(),
								'listing_url'   => get_permalink( $listing->get_id() ),
							],
						]
					) )->send();
				} elseif ( 'trash' === $new_status ) {

					// Send rejection email.
					( new Emails\Listing_Reject(
						[
							'recipient' => $user->get_email(),

							'tokens'    => [
								'user_name'     => $user->get_display_name(),
								'listing_title' => $listing->get_title(),
							],
						]
					) )->send();
				}
			}
		}

		if ( 'publish' === $new_status ) {

			// Get expiration period.
			$expiration_period = absint( get_option( 'hp_listing_expiration_period' ) );

			if ( $expiration_period && ! $listing->get_expiration_time() ) {

				// Set expiration time.
				$listing->set_expiration_time( time() + $expiration_period * DAY_IN_SECONDS )->save();
			}
		}
	}

	/**
	 * Expires listings.
	 */
	public function expire_listings() {

		// Get expired listings.
		$expired_listings = Models\Listing::query()->filter(
			[
				'status'               => 'publish',
				'expiration_time__lte' => time(),
			]
		)->get_all();

		// Update expired listings.
		foreach ( $expired_listings as $listing ) {

			// Update listing.
			$listing->fill(
				[
					'status'          => 'trash',
					'expiration_time' => null,
				]
			)->save();

			// Send email.
			$user = $listing->get_user();

			if ( $user ) {
				( new Emails\Listing_Expire(
					[
						'recipient' => $user->get_email(),

						'tokens'    => [
							'user_name'     => $user->get_display_name(),
							'listing_title' => $listing->get_title(),
						],
					]
				) )->send();
			}
		}

		// Get featured listings.
		$featured_listings = Models\Listing::query()->filter(
			[
				'status'              => 'publish',
				'featuring_time__lte' => time(),
			]
		)->get_all();

		// Update featured listings.
		foreach ( $featured_listings as $listing ) {
			$listing->fill(
				[
					'featured'       => null,
					'featuring_time' => null,
				]
			)->save();
		}
	}

	/**
	 * Adds submission fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_submit_fields( $form ) {

		// Get terms page ID.
		$page_id = reset(
			( get_posts(
				[
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post__in'       => [ absint( get_option( 'hp_page_listing_submission_terms' ) ) ],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			) )
		);

		if ( $page_id ) {

			// Add terms field.
			$form['fields']['_terms'] = [
				'caption'   => sprintf( hp\sanitize_html( __( 'I agree to the <a href="%s" target="_blank">terms and conditions</a>', 'hivepress' ) ), esc_url( get_permalink( $page_id ) ) ),
				'type'      => 'checkbox',
				'required'  => true,
				'_separate' => true,
				'_order'    => 1000,
			];
		}

		return $form;
	}

	/**
	 * Adds post states.
	 *
	 * @param array   $states Post states.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function add_post_states( $states, $post ) {
		if ( 'hp_listing' === $post->post_type ) {

			// Get listing.
			$listing = Models\Listing::query()->get_by_id( $post );

			// Add states.
			if ( $listing->is_featured() ) {
				$states[] = esc_html_x( 'Featured', 'listing', 'hivepress' );
			}

			if ( $listing->is_verified() ) {
				$states[] = esc_html_x( 'Verified', 'listing', 'hivepress' );
			}
		}

		return $states;
	}

	/**
	 * Alters account menu.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function alter_account_menu( $menu ) {
		if ( Models\Listing::query()->filter(
			[
				'user'       => get_current_user_id(),
				'status__in' => [ 'draft', 'pending', 'publish' ],
			]
		)->get_first_id() ) {
			$menu['items']['listings_edit_page'] = [
				'route'  => 'listings_edit_page',
				'_order' => 10,
			];
		}

		return $menu;
	}

	/**
	 * Alters listing view blocks.
	 *
	 * @param array  $blocks Block arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_listing_view_blocks( $blocks, $template ) {

		// Get classes.
		$classes = [];

		if ( $template->get_context( 'listing' )->is_featured() ) {
			$classes[] = 'hp-listing--featured';
		}

		if ( $template->get_context( 'listing' )->is_verified() ) {
			$classes[] = 'hp-listing--verified';
		}

		// Add classes.
		if ( $classes ) {
			$blocks = hp\merge_trees(
				[ 'blocks' => $blocks ],
				[
					'blocks' => [
						'listing_container' => [
							'attributes' => [
								'class' => $classes,
							],
						],
					],
				]
			)['blocks'];
		}

		return $blocks;
	}
}
