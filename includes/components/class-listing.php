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
 * Handles listings.
 */
final class Listing extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Update listing.
		add_action( 'hivepress/v1/models/listing/create', [ $this, 'update_listing' ], 10, 2 );
		add_action( 'hivepress/v1/models/listing/update', [ $this, 'update_listing' ], 10, 2 );

		// Update image.
		add_action( 'hivepress/v1/models/listing/update_images', [ $this, 'update_image' ] );

		// Update status.
		add_action( 'hivepress/v1/models/listing/update_status', [ $this, 'update_status' ], 10, 4 );

		// Expire listings.
		add_action( 'hivepress/v1/events/hourly', [ $this, 'expire_listings' ] );

		// Set category count callback.
		add_filter( 'hivepress/v1/taxonomies', [ $this, 'set_category_count_callback' ] );

		// Alter model fields.
		add_filter( 'hivepress/v1/models/listing', [ $this, 'alter_model_fields' ] );

		// Alter forms.
		add_filter( 'hivepress/v1/forms/listing_submit', [ $this, 'alter_submit_form' ] );
		add_filter( 'hivepress/v1/forms/listing_update', [ $this, 'alter_update_form' ], 10, 2 );

		if ( is_admin() ) {

			// Manage admin columns.
			add_filter( 'manage_hp_listing_posts_columns', [ $this, 'add_listing_admin_columns' ] );
			add_action( 'manage_hp_listing_posts_custom_column', [ $this, 'render_listing_admin_columns' ], 10, 2 );

			// Add post states.
			add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );

			// Alter meta boxes.
			add_filter( 'hivepress/v1/meta_boxes/listing_settings', [ $this, 'alter_listing_settings_meta_box' ] );
			add_filter( 'hivepress/v1/meta_boxes/listing_images', [ $this, 'alter_listing_images_meta_box' ] );
		} else {

			// Set request context.
			add_filter( 'hivepress/v1/components/request/context', [ $this, 'set_request_context' ] );

			// Alter menus.
			add_filter( 'hivepress/v1/menus/user_account', [ $this, 'alter_user_account_menu' ] );
			add_filter( 'hivepress/v1/menus/listing_manage/items', [ $this, 'alter_listing_manage_menu' ], 10, 2 );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/listing_view_block/blocks', [ $this, 'alter_listing_view_blocks' ], 10, 2 );
			add_filter( 'hivepress/v1/templates/listing_edit_block/blocks', [ $this, 'alter_listing_view_blocks' ], 10, 2 );
		}

		parent::__construct( $args );
	}

	/**
	 * Updates listing.
	 *
	 * @param int    $listing_id Listing ID.
	 * @param object $listing Listing object.
	 */
	public function update_listing( $listing_id, $listing ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/listing/update', [ $this, 'update_listing' ] );

		// Check user.
		if ( ! $listing->get_user__id() ) {
			return;
		}

		// Get listing vendor.
		$vendor = null;

		if ( $listing->get_vendor__id() ) {
			$vendor = $listing->get_vendor();
		}

		// Get title.
		$title = get_option( 'hp_listing_title_format' );

		if ( $title ) {
			$title = hp\replace_tokens(
				[
					'listing' => $listing,
					'vendor'  => $vendor,
				],
				$title
			);

			// Update title.
			if ( $listing->get_title() !== $title ) {
				$listing->set_title( $title )->save_title();
			}
		}

		// Update listing user.
		if ( $vendor && $vendor->get_status() === 'publish' ) {
			if ( $vendor->get_user__id() !== $listing->get_user__id() ) {
				$listing->set_user( $vendor->get_user__id() )->save_user();
			}

			return;
		}

		// Get user vendor.
		$vendor = Models\Vendor::query()->filter(
			[
				'status' => [ 'auto-draft', 'draft', 'publish' ],
				'user'   => $listing->get_user__id(),
			]
		)->get_first();

		if ( $listing->get_status() === 'publish' ) {
			if ( ! $vendor ) {

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

				if ( ! $vendor->save(
					[
						'name',
						'description',
						'slug',
						'status',
						'image',
						'user',
					]
				) ) {
					return;
				}
			} elseif ( in_array( $vendor->get_status(), [ 'auto-draft', 'draft' ], true ) ) {

				// Update vendor status.
				$vendor->set_status( 'publish' )->save_status();
			}
		}

		if ( $vendor ) {

			// Update listing vendor.
			$listing->set_vendor( $vendor->get_id() )->save_vendor();
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

		if ( get_option( 'hp_listing_allow_video' ) ) {
			$image_ids = [];

			foreach ( (array) $listing->get_images() as $image ) {
				if ( strpos( $image->get_mime_type(), 'image' ) === 0 ) {
					$image_ids[] = $image->get_id();

					break;
				}
			}
		}

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
	 * @param object $listing Listing.
	 */
	public function update_status( $listing_id, $new_status, $old_status, $listing ) {
		if ( 'pending' === $old_status && get_option( 'hp_listing_enable_moderation' ) ) {

			// Get user.
			$user = $listing->get_user();

			if ( $user ) {
				if ( 'publish' === $new_status ) {

					// Send approval email.
					( new Emails\Listing_Approve(
						[
							'recipient' => $user->get_email(),

							'tokens'    => [
								'user'          => $user,
								'listing'       => $listing,
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
								'user'          => $user,
								'listing'       => $listing,
								'user_name'     => $user->get_display_name(),
								'listing_title' => $listing->get_title(),
							],
						]
					) )->send();

					// Get vendor.
					$vendor = $listing->get_vendor();

					// Delete vendor.
					if ( $vendor && $vendor->get_status() === 'draft' ) {
						$vendor->trash();
					}
				}
			}
		}

		if ( in_array( $new_status, [ 'publish', 'pending' ], true ) ) {

			// Get expiration period.
			$expiration_period = absint( get_option( 'hp_listing_expiration_period' ) );

			if ( $expiration_period && ! $listing->get_expired_time() ) {

				// Set expiration time.
				$listing->set_expired_time( time() + $expiration_period * DAY_IN_SECONDS )->save_expired_time();
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
				'status__in'        => [ 'pending', 'publish' ],
				'expired_time__lte' => time(),
			]
		)->get();

		// Update expired listings.
		foreach ( $expired_listings as $listing ) {

			// Update status.
			$listing->set_status( 'draft' )->save_status();

			// Send email.
			$user = $listing->get_user();

			if ( $user ) {
				( new Emails\Listing_Expire(
					[
						'recipient' => $user->get_email(),

						'tokens'    => [
							'user'          => $user,
							'listing'       => $listing,
							'user_name'     => $user->get_display_name(),
							'listing_title' => $listing->get_title(),
							'listing_url'   => hivepress()->router->get_url( 'listing_edit_page', [ 'listing_id' => $listing->get_id() ] ),
						],
					]
				) )->send();
			}
		}

		// Get storage period.
		$storage_period = absint( get_option( 'hp_listing_storage_period' ) );

		if ( $storage_period ) {

			// Delete stored listings.
			Models\Listing::query()->filter(
				[
					'status'            => 'draft',
					'expired_time__lte' => time() - DAY_IN_SECONDS * $storage_period,
				]
			)->trash();
		}

		// Get featured listings.
		$featured_listings = Models\Listing::query()->filter(
			[
				'status__in'         => [ 'draft', 'pending', 'publish' ],
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
			)->save( [ 'featured', 'featured_time' ] );
		}
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

			// Get term ID.
			$term_id = get_term_by( 'term_taxonomy_id', $term_taxonomy_id )->term_id;

			// Get parent term IDs.
			$parent_term_ids = array_merge( [ $term_id ], get_ancestors( $term_id, 'hp_listing_category', 'taxonomy' ) );

			foreach ( $parent_term_ids as $parent_term_id ) {

				// Get child term IDs.
				$child_term_ids = array_merge( [ $parent_term_id ], get_term_children( $parent_term_id, 'hp_listing_category' ) );

				// Set placeholder.
				$placeholder = implode( ', ', array_fill( 0, count( $child_term_ids ), '%d' ) );

				// Get count.
				$count = (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->term_relationships}
						INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
						INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
						WHERE post_status = 'publish' AND post_type = %s AND term_id IN ( {$placeholder} )",
						array_merge( [ 'hp_listing' ], $child_term_ids )
					)
				);

				// Update count.
				$wpdb->update( $wpdb->term_taxonomy, [ 'count' => $count ], [ 'term_id' => $parent_term_id ] );
			}
		}
	}

	/**
	 * Alters model fields.
	 *
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function alter_model_fields( $model ) {
		if ( get_option( 'hp_listing_title_format' ) ) {
			$model['fields']['title']['required'] = false;
		}

		if ( get_option( 'hp_listing_allow_video' ) ) {
			$model['fields']['images'] = hp\merge_arrays(
				$model['fields']['images'],
				[
					'label'   => esc_html__( 'Gallery', 'hivepress' ),
					'caption' => null,
					'formats' => [ 'mp4', 'webm', 'ogv' ],
				]
			);
		}

		return $model;
	}

	/**
	 * Alters submit form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_submit_form( $form ) {

		// Get terms page ID.
		$page_id = absint( get_option( 'hp_page_listing_submission_terms' ) );

		if ( $page_id ) {

			// Get terms page URL.
			$page_url = get_permalink( $page_id );

			if ( $page_url ) {

				// Add terms field.
				$form['fields']['_terms'] = [
					'caption'   => sprintf( hivepress()->translator->get_string( 'i_agree_to_terms_and_conditions' ), esc_url( $page_url ) ),
					'type'      => 'checkbox',
					'required'  => true,
					'_separate' => true,
					'_order'    => 1000,
				];
			}
		}

		return $form;
	}

	/**
	 * Alters update form.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function alter_update_form( $form_args, $form ) {

		// Get listing.
		$listing = $form->get_model();

		if ( $listing->get_status() === 'draft' && $listing->get_expired_time() && $listing->get_expired_time() < time() ) {

			// Set form arguments.
			$form_args = hp\merge_arrays(
				$form_args,
				[
					'message'  => null,
					'redirect' => hivepress()->router->get_url( 'listing_renew_page', [ 'listing_id' => $listing->get_id() ] ),

					'button'   => [
						'label' => hivepress()->translator->get_string( 'renew_listing' ),
					],
				]
			);
		}

		// Remove title field.
		if ( get_option( 'hp_listing_title_format' ) ) {
			unset( $form_args['fields']['title'] );
		}

		return $form_args;
	}

	/**
	 * Adds listing admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_listing_admin_columns( $columns ) {
		return array_merge(
			array_slice( $columns, 0, 2, true ),
			[
				'vendor' => hivepress()->translator->get_string( 'vendor' ),
			],
			array_slice( $columns, 2, null, true )
		);
	}

	/**
	 * Renders listing admin columns.
	 *
	 * @param string $column Column name.
	 * @param int    $listing_id Listing ID.
	 */
	public function render_listing_admin_columns( $column, $listing_id ) {
		if ( 'vendor' === $column ) {
			$output = '&mdash;';

			// Get name and URL.
			$name = '';
			$url  = '';

			// Get vendor ID.
			$vendor_id = wp_get_post_parent_id( $listing_id );

			if ( $vendor_id ) {
				$name = get_the_title( $vendor_id );
				$url  = hivepress()->router->get_admin_url( 'post', $vendor_id );
			} else {

				// Get user ID.
				$user_id = get_post_field( 'post_author', $listing_id );

				if ( $user_id ) {
					$name = get_the_author_meta( 'display_name', $user_id );
					$url  = hivepress()->router->get_admin_url( 'user', $user_id );
				}
			}

			if ( strlen( $name ) ) {

				// Render link.
				$output = '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
			}

			echo wp_kses_data( $output );
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
	 * Alters listing settings meta box.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function alter_listing_settings_meta_box( $meta_box ) {

		// Get vendor ID.
		$vendor_id = absint( get_post_field( 'post_parent' ) );

		if ( ! $vendor_id || get_post_status( $vendor_id ) !== 'publish' ) {

			// Disable vendor field.
			$meta_box['fields']['vendor'] = array_merge(
				$meta_box['fields']['vendor'],
				[
					'options'     => 'users',
					'option_args' => [],
					'source'      => hivepress()->router->get_url( 'users_resource' ),
					'disabled'    => true,
					'_alias'      => 'post_author',
				]
			);
		}

		return $meta_box;
	}

	/**
	 * Alters listing images meta box.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function alter_listing_images_meta_box( $meta_box ) {

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( get_post() );

		if ( $listing ) {

			// Set image IDs.
			$meta_box['fields']['images']['default'] = $listing->get_images__id();
		}

		return $meta_box;
	}

	/**
	 * Sets request context.
	 *
	 * @param array $context Request context.
	 * @return array
	 */
	public function set_request_context( $context ) {

		// Get cached listing count.
		$listing_count = hivepress()->cache->get_user_cache( get_current_user_id(), 'listing_count', 'models/listing' );

		if ( is_null( $listing_count ) ) {

			// Get listing count.
			$listing_count = Models\Listing::query()->filter(
				[
					'status__in' => [ 'draft', 'pending', 'publish' ],
					'user'       => get_current_user_id(),
				]
			)->get_count();

			// Cache listing count.
			hivepress()->cache->set_user_cache( get_current_user_id(), 'listing_count', 'models/listing', $listing_count );
		}

		// Set request context.
		$context['listing_count'] = $listing_count;

		return $context;
	}

	/**
	 * Alters user account menu.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function alter_user_account_menu( $menu ) {
		if ( hivepress()->request->get_context( 'listing_count' ) ) {
			$menu['items']['listings_edit'] = [
				'route'  => 'listings_edit_page',
				'_order' => 10,
			];
		}

		return $menu;
	}

	/**
	 * Alters listing manage menu.
	 *
	 * @param array  $items Menu items.
	 * @param object $menu Menu object.
	 * @return array
	 */
	public function alter_listing_manage_menu( $items, $menu ) {

		// Get listing.
		$listing = $menu->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {

			// Add menu items.
			if ( $listing->get_status() === 'publish' ) {
				$items['listing_view'] = [
					'label'  => hivepress()->translator->get_string( 'details' ),
					'url'    => hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ),
					'_order' => 5,
				];
			}

			if ( get_current_user_id() === $listing->get_user__id() ) {
				$items['listing_edit'] = [
					'label'  => esc_html__( 'Edit', 'hivepress' ),
					'url'    => hivepress()->router->get_url( 'listing_edit_page', [ 'listing_id' => $listing->get_id() ] ),
					'_order' => 1000,
				];
			}
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
				$blocks = hivepress()->template->merge_blocks(
					$blocks,
					[
						'listing_container' => [
							'attributes' => [
								'class' => $classes,
							],
						],
					]
				);
			}
		}

		return $blocks;
	}
}
