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
		add_action( 'hivepress/v1/models/listing/create', [ $this, 'update_vendor' ] );
		add_action( 'hivepress/v1/models/listing/update', [ $this, 'update_vendor' ] );

		// Update image.
		add_action( 'hivepress/v1/models/listing/update_images', [ $this, 'update_image' ] );

		// Update status.
		add_action( 'hivepress/v1/models/listing/update_status', [ $this, 'update_status' ], 10, 3 );

		// Expire listings.
		add_action( 'hivepress/v1/events/hourly', [ $this, 'expire_listings' ] );

		// Add submission fields.
		add_filter( 'hivepress/v1/forms/listing_submit', [ $this, 'add_submission_fields' ] );

		// Set category count callback.
		add_filter( 'hivepress/v1/taxonomies', [ $this, 'set_category_count_callback' ] );

		if ( is_admin() ) {

			// Add post states.
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

		} else {

			// Alter account menu.
			add_filter( 'hivepress/v1/menus/user_account', [ $this, 'alter_account_menu' ] );

			// Alter manage menu.
			add_filter( 'hivepress/v1/menus/listing_manage/items', [ $this, 'alter_manage_menu' ], 10, 2 );

			// Alter breadcrumb menu.
			add_filter( 'hivepress/v1/menus/breadcrumb/items', [ $this, 'alter_breadcrumb_menu' ], 10, 2 );

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

		if ( ! $listing->get_user__id() ) {
			return;
		}

		// Get vendor.
		$vendor = Models\Vendor::query()->filter( [ 'user' => $listing->get_user__id() ] )->get_first();

		if ( empty( $vendor ) ) {

			// Get user.
			$user = $listing->get_user();

			// Add vendor.
			$vendor = ( new Models\Vendor() )->fill(
				[
					'name'        => $user->get_display_name(),
					'description' => $user->get_description(),
					'slug'        => $user->get_username(),
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
						'parent_model' => 'listing',
						'parent'       => $listing->get_id(),
					]
				)->get();

				foreach ( $attachments as $attachment ) {
					$attachment->set_user( $listing->get_user__id() )->save();
				}
			}

			// Set vendor.
			wp_update_post(
				[
					'ID'          => $listing->get_id(),
					'post_parent' => $vendor->get_id(),
				]
			);
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

		// Get image IDs.
		$image_ids = $listing->get_images__id();

		// Set image.
		if ( $image_ids ) {
			set_post_thumbnail( $listing->get_id(), hp\get_first_array_value( $image_ids ) );
		} else {
			delete_post_thumbnail( $listing->get_id() );
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

			if ( $expiration_period && ! $listing->get_expired_time() ) {

				// Set expiration time.
				$listing->set_expired_time( time() + $expiration_period * DAY_IN_SECONDS )->save();
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
				'status'            => 'publish',
				'expired_time__lte' => time(),
			]
		)->get();

		// Update expired listings.
		foreach ( $expired_listings as $listing ) {

			// Update listing.
			$listing->fill(
				[
					'status'       => 'trash',
					'expired_time' => null,
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
				'status'             => 'publish',
				'featured_time__lte' => time(),
			]
		)->get();

		// Update featured listings.
		foreach ( $featured_listings as $listing ) {
			$listing->fill(
				[
					'featured'      => false,
					'featured_time' => null,
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
	public function add_submission_fields( $form ) {

		// Get terms page ID.
		$page_id = hp\get_first_array_value(
			get_posts(
				[
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post__in'       => [ absint( get_option( 'hp_page_listing_submission_terms' ) ) ],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			)
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
	 * Sets category count callback.
	 *
	 * @param array $taxonomies Taxonomy arguments.
	 * @return array
	 */
	public function set_category_count_callback( $taxonomies ) {
		return hp\merge_arrays(
			$taxonomies,
			[
				'listing_category' => [
					'update_count_callback' => [ $this, 'update_category_count' ],
				],
			]
		);
	}

	/**
	 * Updates category count.
	 *
	 * @param array $term_taxonomy_ids Term taxonomy IDs.
	 */
	public function update_category_count( $term_taxonomy_ids ) {
		global $wpdb;

		foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {

			// Get count.
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->term_relationships}
					INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
					WHERE post_status = 'publish' AND post_type = %s AND term_taxonomy_id = %d",
					'hp_listing',
					$term_taxonomy_id
				)
			);

			// Update count.
			$wpdb->update( $wpdb->term_taxonomy, [ 'count' => $count ], [ 'term_taxonomy_id' => $term_taxonomy_id ] );
		}
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
			$menu['items']['listings_edit'] = [
				'route'  => 'listings_edit_page',
				'_order' => 10,
			];
		}

		return $menu;
	}

	/**
	 * Alters manage menu.
	 *
	 * @param array  $items Menu items.
	 * @param object $menu Menu object.
	 * @return array
	 */
	public function alter_manage_menu( $items, $menu ) {

		// Get listing.
		$listing = $menu->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {

			// Add menu items.
			$items = hp\merge_arrays(
				$items,
				[
					'listing_view' => [
						'label'  => esc_html__( 'View', 'hivepress' ),
						'url'    => hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ),
						'_order' => 10,
					],

					'listing_edit' => [
						'label'  => esc_html__( 'Edit', 'hivepress' ),
						'url'    => hivepress()->router->get_url( 'listing_edit_page', [ 'listing_id' => $listing->get_id() ] ),
						'_order' => 20,
					],
				]
			);
		}

		return $items;
	}

	/**
	 * Alters breadcrumb menu.
	 *
	 * @param array  $items Menu items.
	 * @param object $menu Menu object.
	 * @return array
	 */
	public function alter_breadcrumb_menu( $items, $menu ) {

		// Get listing.
		$listing = $menu->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {

			// Add menu items.
			$items = hp\merge_arrays(
				$items,
				[
					'listings_view' => [
						'label'  => hivepress()->translator->get_string( 'all_listings' ),
						'url'    => hivepress()->router->get_url( 'listings_view_page' ),
						'_order' => 10,
					],
				]
			);
		}

		return $items;
	}

	/**
	 * Alters listing view blocks.
	 *
	 * @param array  $blocks Block arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_listing_view_blocks( $blocks, $template ) {

		// Get listing.
		$listing = $template->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {

			// Get classes.
			$classes = [];

			if ( $listing->is_featured() ) {
				$classes[] = 'hp-listing--featured';
			}

			if ( $listing->is_verified() ) {
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
		}

		return $blocks;
	}
}
