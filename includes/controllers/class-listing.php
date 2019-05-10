<?php
/**
 * Listing controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Forms;
use HivePress\Menus;
use HivePress\Blocks;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing controller class.
 *
 * @class Listing
 */
class Listing extends Controller {

	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Controller arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					[
						'path'      => '/listings',
						'rest'      => true,

						'endpoints' => [
							[
								'path'    => '/(?P<id>\d+)',
								'methods' => 'POST',
								'action'  => 'update_listing',
							],

							[
								'path'    => '/(?P<id>\d+)/report',
								'methods' => 'POST',
								'action'  => 'report_listing',
							],

							[
								'path'    => '/(?P<id>\d+)',
								'methods' => 'DELETE',
								'action'  => 'delete_listing',
							],
						],
					],

					'view_listings'   => [
						'match'  => 'is_listings_view_page',
						'action' => 'render_listings_view_page',
					],

					'view_listing'    => [
						'match'  => 'is_listing_view_page',
						'action' => 'render_listing_view_page',
					],

					'edit_listings'   => [
						'title'    => esc_html__( 'My Listings', 'hivepress' ),
						'path'     => '/account/listings',
						'redirect' => 'redirect_listings_edit_page',
						'action'   => 'render_listings_edit_page',
					],

					'edit_listing'    => [
						'title'    => esc_html__( 'Edit Listing', 'hivepress' ),
						'path'     => '/account/listings/(?P<listing_id>\d+)',
						'redirect' => 'redirect_listing_edit_page',
						'action'   => 'render_listing_edit_page',
					],

					'submit_listing'  => [
						'path'     => '/submit-listing',
						'redirect' => 'redirect_listing_submit_page',
					],

					'submit_category' => [
						'title'    => esc_html__( 'Select Category', 'hivepress' ),
						'path'     => '/submit-listing/category/?(?P<listing_category_id>\d+)?',
						'redirect' => 'redirect_listing_submit_category_page',
						'action'   => 'render_listing_submit_category_page',
					],

					'submit_details'  => [
						'title'    => esc_html__( 'Add Details', 'hivepress' ),
						'path'     => '/submit-listing/details',
						'redirect' => 'redirect_listing_submit_details_page',
						'action'   => 'render_listing_submit_details_page',
					],

					'submit_complete' => [
						'title'    => esc_html__( 'Listing Submitted', 'hivepress' ),
						'path'     => '/submit-listing/complete',
						'redirect' => 'redirect_listing_submit_complete_page',
						'action'   => 'render_listing_submit_complete_page',
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Updates listing.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function update_listing( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get listing.
		$listing = Models\Listing::get( $request->get_param( 'id' ) );

		if ( is_null( $listing ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) && ( get_current_user_id() !== $listing->get_user_id() || ! in_array( $listing->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = new Forms\Listing_Update();

		if ( $listing->get_status() === 'auto-draft' ) {
			$form = new Forms\Listing_Submit();
		}

		$form->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Update listing.
		$listing->fill( $form->get_values() );

		if ( ! $listing->save() ) {
			return hp\rest_error( 400, esc_html__( 'Error updating listing', 'hivepress' ) );
		}

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $listing->get_id(),
				],
			],
			200
		);
	}

	/**
	 * Reports listing.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function report_listing( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get listing.
		$listing = Models\Listing::get( $request->get_param( 'id' ) );

		if ( is_null( $listing ) || $listing->get_status() !== 'publish' ) {
			return hp\rest_error( 404 );
		}

		// Validate form.
		$form = new Forms\Listing_Report();

		$form->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Send email.
		( new Emails\Listing_Report(
			[
				'recipient' => get_option( 'admin_email' ),
				'tokens'    => [
					'listing_title' => $listing->get_title(),
					'listing_url'   => get_permalink( $listing->get_id() ),
					'report_reason' => $form->get_value( 'reason' ),
				],
			]
		) )->send();

		return new \WP_Rest_Response(
			[
				'data' => [
					'id' => $listing->get_id(),
				],
			],
			200
		);
	}

	/**
	 * Deletes listing.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function delete_listing( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get listing.
		$listing = Models\Listing::get( $request->get_param( 'id' ) );

		if ( is_null( $listing ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'delete_others_posts' ) && ( get_current_user_id() !== $listing->get_user_id() || ! in_array( $listing->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) {
			return hp\rest_error( 403 );
		}

		// Delete listing.
		if ( ! $listing->delete() ) {
			return hp\rest_error( 400, esc_html__( 'Error deleting listing', 'hivepress' ) );
		}

		return new \WP_Rest_Response( (object) [], 204 );
	}

	/**
	 * Matches listings view page.
	 *
	 * @return bool
	 */
	public function is_listings_view_page() {

		// Get page ID.
		$page_id = absint( get_option( 'hp_page_listings' ) );

		return ( 0 !== $page_id && is_page( $page_id ) ) || is_post_type_archive( 'hp_listing' ) || is_tax( 'hp_listing_category' );
	}

	/**
	 * Matches listings view page.
	 *
	 * @return string
	 */
	public function render_listings_view_page() {
		$output = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();

		// Get category ID.
		$category_id = absint( hp\get_array_value( $_GET, 'category' ) );

		if ( is_tax() ) {
			$category_id = get_queried_object_id();
		}

		if ( ( is_page() && get_option( 'hp_page_listings_display_subcategories' ) ) || ( 0 !== $category_id && get_term_meta( $category_id, 'hp_display_subcategories', true ) ) ) {

			// Render categories.
			$output .= ( new Blocks\Template(
				[
					'template_name'       => 'listing_categories_view_page',
					'listing_category_id' => 0 !== $category_id ? $category_id : null,
				]
			) )->render();
		} else {

			// Query listings.
			if ( is_page() ) {
				query_posts(
					[
						'post_type'      => 'hp_listing',
						'post_status'    => 'publish',
						'posts_per_page' => absint( get_option( 'hp_listings_per_page' ) ),
						'paged'          => hp\get_current_page(),
					]
				);
			}

			// Render listings.
			$output .= ( new Blocks\Template( [ 'template_name' => 'listings_view_page' ] ) )->render();
		}

		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}

	/**
	 * Checks listing view page.
	 *
	 * @return bool
	 */
	public function is_listing_view_page() {
		return is_singular( 'hp_listing' );
	}

	/**
	 * Renders listing view page.
	 *
	 * @return string
	 */
	public function render_listing_view_page() {
		the_post();

		$output  = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();
		$output .= ( new Blocks\Listing( [ 'template_name' => 'listing_view_page' ] ) )->render();
		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}

	/**
	 * Redirects listings edit page.
	 *
	 * @return mixed
	 */
	public function redirect_listings_edit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), User::get_url( 'login_user' ) );
		}

		// Check listings.
		if ( hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => [ 'draft', 'pending', 'publish' ],
				'author'      => get_current_user_id(),
			]
		) === 0 ) {
			return true;
		}
	}

	/**
	 * Renders listings edit page.
	 *
	 * @return string
	 */
	public function render_listings_edit_page() {

		// Query listings.
		query_posts(
			[
				'post_type'      => 'hp_listing',
				'post_status'    => [ 'draft', 'pending', 'publish' ],
				'author'         => get_current_user_id(),
				'posts_per_page' => -1,
			]
		);

		// Render page.
		$output  = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();
		$output .= ( new Blocks\Template( [ 'template_name' => 'listings_edit_page' ] ) )->render();
		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}

	/**
	 * Redirects listing edit page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_edit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), User::get_url( 'login_user' ) );
		}

		// Get listing.
		$listing = Models\Listing::get( get_query_var( 'hp_listing_id' ) );

		if ( is_null( $listing ) || get_current_user_id() !== $listing->get_user_id() || ! in_array( $listing->get_status(), [ 'draft', 'publish' ], true ) ) {
			return self::get_url( 'edit_listings' );
		}

		return false;
	}

	/**
	 * Renders listing edit page.
	 *
	 * @return string
	 */
	public function render_listing_edit_page() {
		$output = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();

		$output .= ( new Blocks\Template(
			[
				'template_name' => 'listing_edit_page',
				'listing_id'    => absint( get_query_var( 'hp_listing_id' ) ),
			]
		) )->render();

		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}

	/**
	 * Redirects listing submit page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), User::get_url( 'login_user' ) );
		}

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'auto-draft',
				'post_parent' => null,
				'author'      => get_current_user_id(),
			]
		);

		if ( 0 === $listing_id ) {

			// Add listing.
			$listing_id = wp_insert_post(
				[
					'post_type'   => 'hp_listing',
					'post_status' => 'auto-draft',
					'post_author' => get_current_user_id(),
				]
			);
		}

		// Check listing.
		if ( 0 !== $listing_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Redirects listing submit category page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_category_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), User::get_url( 'login_user' ) );
		}

		// Check categories.
		if ( wp_count_terms( 'hp_listing_category', [ 'hide_empty' => false ] ) === 0 ) {
			return true;
		}

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'auto-draft',
				'post_parent' => null,
				'author'      => get_current_user_id(),
			]
		);

		// Get category.
		$category = get_term( absint( get_query_var( 'hp_listing_category_id' ) ), 'hp_listing_category' );

		if ( ! is_null( $category ) && ! is_wp_error( $category ) ) {

			// Get category IDs.
			$category_ids = get_term_children( $category->term_id, 'hp_listing_category' );

			if ( empty( $category_ids ) ) {

				// Set category.
				wp_set_post_terms( $listing_id, [ $category->term_id ], 'hp_listing_category' );

				return true;
			}
		}

		// Check category.
		if ( has_term( '', 'hp_listing_category', $listing_id ) ) {
			return null;
		}

		return false;
	}

	/**
	 * Renders listing submit category page.
	 *
	 * @return string
	 */
	public function render_listing_submit_category_page() {
		$output = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();

		$output .= ( new Blocks\Template(
			[
				'template_name'       => 'listing_submit_category_page',
				'listing_category_id' => absint( get_query_var( 'hp_listing_category_id' ) ),
			]
		) )->render();

		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}

	/**
	 * Redirects listing submit details page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_details_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), User::get_url( 'login_user' ) );
		}

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'auto-draft',
				'post_parent' => null,
				'author'      => get_current_user_id(),
			]
		);

		// Check listing.
		if ( '' !== get_the_title( $listing_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders listing submit details page.
	 *
	 * @return string
	 */
	public function render_listing_submit_details_page() {

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'auto-draft',
				'post_parent' => null,
				'author'      => get_current_user_id(),
			]
		);

		// Render page.
		$output = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();

		$output .= ( new Blocks\Template(
			[
				'template_name' => 'listing_submit_details_page',
				'listing_id'    => $listing_id,
			]
		) )->render();

		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}

	/**
	 * Redirects listing submit complete page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_complete_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), User::get_url( 'login_user' ) );
		}

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'auto-draft',
				'post_parent' => null,
				'author'      => get_current_user_id(),
			]
		);

		// Update listing.
		$status = get_option( 'hp_listing_enable_moderation' ) ? 'pending' : 'publish';

		wp_update_post(
			[
				'ID'          => $listing_id,
				'post_status' => $status,
			]
		);

		// Send email.
		( new Emails\Listing_Submit(
			[
				'recipient' => get_option( 'admin_email' ),
				'tokens'    => [
					'listing_title' => get_the_title( $listing_id ),
					'listing_url'   => 'publish' === $status ? get_permalink( $listing_id ) : get_preview_post_link( $listing_id ),
				],
			]
		) )->send();

		if ( 'publish' === $status ) {
			return get_permalink( $listing_id );
		} else {
			return null;
		}

		return false;
	}

	/**
	 * Renders listing submit complete page.
	 *
	 * @return string
	 */
	public function render_listing_submit_complete_page() {

		// Get listing ID.
		$listing_id = hp\get_post_id(
			[
				'post_type'   => 'hp_listing',
				'post_status' => 'pending',
				'post_parent' => null,
				'author'      => get_current_user_id(),
			]
		);

		// Render page.
		$output = ( new Blocks\Element( [ 'file_path' => 'header' ] ) )->render();

		$output .= ( new Blocks\Template(
			[
				'template_name' => 'listing_submit_complete_page',
				'listing'       => Models\Listing::get( $listing_id ),
			]
		) )->render();

		$output .= ( new Blocks\Element( [ 'file_path' => 'footer' ] ) )->render();

		return $output;
	}
}
