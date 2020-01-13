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
use HivePress\Blocks;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing controller class.
 *
 * @class Listing
 */
final class Listing extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [

					/**
					 * Listings API route.
					 *
					 * @resource Listings
					 * @description The listings API allows you to update and delete listings.
					 */
					'listings_resource'            => [
						'path' => '/listings',
						'rest' => true,
					],

					'listing_resource'             => [
						'base' => 'listings_resource',
						'path' => '/(?P<listing_id>\d+)',
						'rest' => true,
					],

					/**
					 * Updates listing.
					 *
					 * @endpoint Update listing
					 * @route /listings/<id>
					 * @method POST
					 * @param string $title Title.
					 * @param string $description Description.
					 */
					'listing_update_action'        => [
						'base'   => 'listing_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_listing' ],
						'rest'   => true,
					],

					'listing_report_action'        => [
						'base'   => 'listing_resource',
						'path'   => '/report',
						'method' => 'POST',
						'action' => [ $this, 'report_listing' ],
						'rest'   => true,
					],

					/**
					 * Deletes listing.
					 *
					 * @endpoint Delete listing
					 * @route /listings/<id>
					 * @method DELETE
					 */
					'listing_delete_action'        => [
						'base'   => 'listing_resource',
						'method' => 'DELETE',
						'action' => [ $this, 'delete_listing' ],
						'rest'   => true,
					],

					'listings_view_page'           => [
						'url'    => [ $this, 'get_listings_view_url' ],
						'match'  => [ $this, 'is_listings_view_page' ],
						'action' => [ $this, 'render_listings_view_page' ],
					],

					'listing_view_page'            => [
						'url'    => [ $this, 'get_listing_view_url' ],
						'match'  => [ $this, 'is_listing_view_page' ],
						'action' => [ $this, 'render_listing_view_page' ],
					],

					'listings_edit_page'           => [
						'title'    => hivepress()->translator->get_string( 'listings' ),
						'base'     => 'user_account_page',
						'path'     => '/listings',
						'redirect' => [ $this, 'redirect_listings_edit_page' ],
						'action'   => [ $this, 'render_listings_edit_page' ],
					],

					'listing_edit_page'            => [
						'title'    => hivepress()->translator->get_string( 'edit_listing' ),
						'base'     => 'listings_edit_page',
						'path'     => '/(?P<listing_id>\d+)',
						'redirect' => [ $this, 'redirect_listing_edit_page' ],
						'action'   => [ $this, 'render_listing_edit_page' ],
					],

					'listing_submit_page'          => [
						'path'     => '/submit-listing',
						'redirect' => [ $this, 'redirect_listing_submit_page' ],
					],

					'listing_submit_category_page' => [
						'title'    => esc_html_x( 'Select Category', 'imperative', 'hivepress' ),
						'base'     => 'listing_submit_page',
						'path'     => '/category/?(?P<listing_category_id>\d+)?',
						'redirect' => [ $this, 'redirect_listing_submit_category_page' ],
						'action'   => [ $this, 'render_listing_submit_category_page' ],
					],

					'listing_submit_details_page'  => [
						'title'    => esc_html_x( 'Add Details', 'imperative', 'hivepress' ),
						'base'     => 'listing_submit_page',
						'path'     => '/details',
						'redirect' => [ $this, 'redirect_listing_submit_details_page' ],
						'action'   => [ $this, 'render_listing_submit_details_page' ],
					],

					'listing_submit_complete_page' => [
						'title'    => hivepress()->translator->get_string( 'listing_submitted' ),
						'base'     => 'listing_submit_page',
						'path'     => '/complete',
						'redirect' => [ $this, 'redirect_listing_submit_complete_page' ],
						'action'   => [ $this, 'render_listing_submit_complete_page' ],
					],

					'listing_category_view_page'   => [
						'url' => [ $this, 'get_listing_category_view_url' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
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
		$listing = Models\Listing::query()->get_by_id( $request->get_param( 'listing_id' ) );

		if ( empty( $listing ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) && ( get_current_user_id() !== $listing->get_user__id() || ! in_array( $listing->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		if ( $listing->get_status() === 'auto-draft' ) {
			$form = new Forms\Listing_Submit( [ 'model' => $listing ] );
		} else {
			$form = new Forms\Listing_Update( [ 'model' => $listing ] );
		}

		$form->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Update listing.
		$listing->fill( $form->get_values() );

		if ( ! $listing->save() ) {
			return hp\rest_error( 400 );
		}

		return hp\rest_response(
			200,
			[
				'id' => $listing->get_id(),
			]
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
		$listing = Models\Listing::query()->get_by_id( $request->get_param( 'listing_id' ) );

		if ( empty( $listing ) || $listing->get_status() !== 'publish' ) {
			return hp\rest_error( 404 );
		}

		// Validate form.
		$form = ( new Forms\Listing_Report() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Send email.
		( new Emails\Listing_Report(
			[
				'recipient' => get_option( 'admin_email' ),

				'tokens'    => [
					'listing_title'  => $listing->get_title(),
					'listing_url'    => get_permalink( $listing->get_id() ),
					'report_details' => $form->get_value( 'details' ),
				],
			]
		) )->send();

		return hp\rest_response(
			200,
			[
				'id' => $listing->get_id(),
			]
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
		$listing = Models\Listing::query()->get_by_id( $request->get_param( 'listing_id' ) );

		if ( empty( $listing ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'delete_others_posts' ) && ( get_current_user_id() !== $listing->get_user__id() || ! in_array( $listing->get_status(), [ 'auto-draft', 'draft', 'publish' ], true ) ) ) {
			return hp\rest_error( 403 );
		}

		// Delete listing.
		if ( ! $listing->delete() ) {
			return hp\rest_error( 400 );
		}

		return hp\rest_response( 204 );
	}

	/**
	 * Gets listings view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_listings_view_url( $params ) {
		return get_post_type_archive_link( 'hp_listing' );
	}

	/**
	 * Matches listings view URL.
	 *
	 * @return bool
	 */
	public function is_listings_view_page() {

		// Get page ID.
		$page_id = absint( get_option( 'hp_page_listings' ) );

		return ( $page_id && is_page( $page_id ) ) || is_post_type_archive( 'hp_listing' ) || is_tax( 'hp_listing_category' );
	}

	/**
	 * Renders listings view page.
	 *
	 * @return string
	 */
	public function render_listings_view_page() {

		// Get category.
		$category    = null;
		$category_id = is_tax() ? get_queried_object_id() : absint( hp\get_array_value( $_GET, 'category' ) );

		if ( $category_id ) {
			$category = Models\Listing_Category::query()->get_by_id( $category_id );
		}

		if ( ( is_page() && get_option( 'hp_page_listings_display_categories' ) ) || ( $category && get_term_meta( $category->get_id(), 'hp_display_subcategories', true ) ) ) {

			// Render categories.
			return ( new Blocks\Template(
				[
					'template' => 'listing_categories_view_page',

					'context'  => [
						'listing_category' => $category,
					],
				]
			) )->render();
		} else {
			if ( is_page() ) {

				// Query listings.
				query_posts(
					Models\Listing::query()->filter(
						[
							'status'     => 'publish',
							'id__not_in' => hivepress()->request->get_context( 'featured_ids', [] ),
						]
					)->order( [ 'created_date' => 'desc' ] )
					->limit( get_option( 'hp_listings_per_page' ) )
					->paginate( hivepress()->request->get_context( 'page_number' ) )
					->get_args()
				);
			}

			// Render listings.
			return ( new Blocks\Template(
				[
					'template' => 'listings_view_page',

					'context'  => [
						'listing_category' => $category,
					],
				]
			) )->render();
		}
	}

	/**
	 * Gets listing view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_listing_view_url( $params ) {
		return get_permalink( hp\get_array_value( $params, 'listing_id' ) );
	}

	/**
	 * Matches listing view URL.
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

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( get_post() );

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'listing_view_page',

				'context'  => [
					'listing' => $listing,
				],
			]
		) )->render();
	}

	/**
	 * Redirects listings edit page.
	 *
	 * @return mixed
	 */
	public function redirect_listings_edit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_url(
				'user_login_page',
				[
					'redirect' => hivepress()->router->get_current_url(),
				]
			);
		}

		// Check listings.
		if ( ! Models\Listing::query()->filter(
			[
				'status__in' => [ 'draft', 'pending', 'publish' ],
				'user'       => get_current_user_id(),
			]
		)->get_first_id() ) {
			return hivepress()->router->get_url( 'user_account_page' );
		}

		return false;
	}

	/**
	 * Renders listings edit page.
	 *
	 * @return string
	 */
	public function render_listings_edit_page() {

		// Query listings.
		query_posts(
			Models\Listing::query()->filter(
				[
					'status__in' => [ 'draft', 'pending', 'publish' ],
					'user'       => get_current_user_id(),
				]
			)->order( [ 'created_date' => 'desc' ] )
			->get_args()
		);

		// Render template.
		return ( new Blocks\Template( [ 'template' => 'listings_edit_page' ] ) )->render();
	}

	/**
	 * Redirects listing edit page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_edit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_url(
				'user_login_page',
				[
					'redirect' => hivepress()->router->get_current_url(),
				]
			);
		}

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( hivepress()->request->get_param( 'listing_id' ) );

		if ( empty( $listing ) || get_current_user_id() !== $listing->get_user__id() || ! in_array( $listing->get_status(), [ 'draft', 'publish' ], true ) ) {
			return hivepress()->router->get_url( 'listings_edit_page' );
		}

		// Set listing.
		hivepress()->request->set_context( 'listing', $listing );

		return false;
	}

	/**
	 * Renders listing edit page.
	 *
	 * @return string
	 */
	public function render_listing_edit_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listing_edit_page',

				'context'  => [
					'listing' => hivepress()->request->get_context( 'listing' ),
				],
			]
		) )->render();
	}

	/**
	 * Redirects listing submit page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_url(
				'user_login_page',
				[
					'redirect' => hivepress()->router->get_current_url(),
				]
			);
		}

		// Check permissions.
		if ( ! get_option( 'hp_listing_enable_submission' ) ) {
			return home_url( '/' );
		}

		// Get listing.
		$listing = Models\Listing::query()->filter(
			[
				'status' => 'auto-draft',
				'user'   => get_current_user_id(),
			]
		)->set_args( [ 'meta_key' => 'hp_drafted' ] )
		->get_first();

		if ( empty( $listing ) ) {

			// Add listing.
			$listing = ( new Models\Listing() )->fill(
				[
					'status' => 'auto-draft',
					'user'   => get_current_user_id(),
				]
			);

			if ( ! $listing->save() ) {
				return home_url( '/' );
			}

			// Set draft flag.
			update_post_meta( $listing->get_id(), 'hp_drafted', '1' );
		}

		// Set request context.
		hivepress()->request->set_context( 'listing', $listing );

		return true;
	}

	/**
	 * Redirects listing submit category page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_category_page() {

		// Check categories.
		if ( ! Models\Listing_Category::query()->get_first_id() ) {
			return true;
		}

		// Get listing.
		$listing = hivepress()->request->get_context( 'listing' );

		if ( hivepress()->request->get_param( 'listing_category_id' ) ) {

			// Get category.
			$category = Models\Listing_Category::query()->get_by_id( hivepress()->request->get_param( 'listing_category_id' ) );

			if ( $category ) {
				if ( ! $category->get_children__id() ) {

					// Set listing category.
					$listing->set_categories( $category->get_id() )->save();
				}

				// Set request context.
				hivepress()->request->set_context( 'listing_category', $category );
			}
		}

		// Check category.
		if ( $listing->get_categories__id() ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders listing submit category page.
	 *
	 * @return string
	 */
	public function render_listing_submit_category_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listing_submit_category_page',

				'context'  => [
					'listing_category' => hivepress()->request->get_context( 'listing_category' ),
				],
			]
		) )->render();
	}

	/**
	 * Redirects listing submit details page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_details_page() {

		// Get listing.
		$listing = hivepress()->request->get_context( 'listing' );

		// Check listing.
		if ( $listing->get_title() ) {
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
		return ( new Blocks\Template(
			[
				'template' => 'listing_submit_details_page',

				'context'  => [
					'listing' => hivepress()->request->get_context( 'listing' ),
				],
			]
		) )->render();
	}

	/**
	 * Redirects listing submit complete page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_complete_page() {

		// Get listing.
		$listing = hivepress()->request->get_context( 'listing' );

		// Get status.
		$status = get_option( 'hp_listing_enable_moderation' ) ? 'pending' : 'publish';

		// Update listing.
		$listing->set_status( $status )->save();

		// Remove draft flag.
		delete_post_meta( $listing->get_id(), 'hp_drafted' );

		// Send email.
		( new Emails\Listing_Submit(
			[
				'recipient' => get_option( 'admin_email' ),

				'tokens'    => [
					'listing_title' => $listing->get_title(),
					'listing_url'   => 'publish' === $status ? get_permalink( $listing->get_id() ) : get_preview_post_link( $listing->get_id() ),
				],
			]
		) )->send();

		if ( 'publish' === $status ) {
			return get_permalink( $listing->get_id() );
		}

		return false;
	}

	/**
	 * Renders listing submit complete page.
	 *
	 * @return string
	 */
	public function render_listing_submit_complete_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listing_submit_complete_page',

				'context'  => [
					'listing' => hivepress()->request->get_context( 'listing' ),
				],
			]
		) )->render();
	}

	/**
	 * Gets listing category view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_listing_category_view_url( $params ) {
		return get_term_link( hp\get_array_value( $params, 'listing_category_id' ) );
	}
}
