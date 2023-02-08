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
 * Manages listings.
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
					'listings_resource'            => [
						'path'   => '/listings',
						'method' => 'GET',
						'action' => [ $this, 'get_listings' ],
						'rest'   => true,
					],

					/**
					* @OA\Parameter(
					*     name="listing_id",
					*     description="Listing ID.",
					*     in="path",
					*     required=true,
					*     @OA\Schema(type="integer"),
					* ),
					*/
					'listing_resource'             => [
						'base' => 'listings_resource',
						'path' => '/(?P<listing_id>\d+)',
						'rest' => true,
					],

					/**
					 * @OA\Post(
					 *     path="/listings/{listing_id}",
					 *     summary="Update a listing",
					 *     description="In addition to the default listing fields, you can also update custom fields added via the listing attributes or HivePress extensions.",
					 *     tags={"Listings"},
					 *     @OA\Parameter(ref="#/components/parameters/listing_id"),
					 *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Listing")),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'listing_update_action'        => [
						'base'   => 'listing_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_listing' ],
						'rest'   => true,
					],

					/**
					 * @OA\Post(
					 *     path="/listings/{listing_id}/hide",
					 *     summary="Hide a listing",
					 *     description="Each new request hides or unhides a listing.",
					 *     tags={"Listings"},
					 *     @OA\Parameter(ref="#/components/parameters/listing_id"),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'listing_hide_action'          => [
						'base'   => 'listing_resource',
						'path'   => '/hide',
						'method' => 'POST',
						'action' => [ $this, 'hide_listing' ],
						'rest'   => true,
					],

					/**
					 * @OA\Post(
					 *     path="/listings/{listing_id}/report",
					 *     summary="Report a listing",
					 *     description="Sends an email to the site administrator.",
					 *     tags={"Listings"},
					 *     @OA\Parameter(ref="#/components/parameters/listing_id"),
					 *     @OA\RequestBody(
					 *       @OA\JsonContent(
					 *         @OA\Property(property="details", type="string", description="Report details.")
					 *       ),
					 *     ),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'listing_report_action'        => [
						'base'   => 'listing_resource',
						'path'   => '/report',
						'method' => 'POST',
						'action' => [ $this, 'report_listing' ],
						'rest'   => true,
					],

					/**
					 * @OA\Delete(
					 *     path="/listings/{listing_id}",
					 *     summary="Delete a listing",
					 *     tags={"Listings"},
					 *     @OA\Parameter(ref="#/components/parameters/listing_id"),
					 *     @OA\Response(response="204", description="")
					 * )
					 */
					'listing_delete_action'        => [
						'base'   => 'listing_resource',
						'method' => 'DELETE',
						'action' => [ $this, 'delete_listing' ],
						'rest'   => true,
					],

					'listings_view_page'           => [
						'url'      => [ $this, 'get_listings_view_url' ],
						'match'    => [ $this, 'is_listings_view_page' ],
						'action'   => [ $this, 'render_listings_view_page' ],

						'redirect' => [
							[
								'callback' => [ $this, 'redirect_listings_view_page' ],
								'_order'   => 5,
							],
						],
					],

					'listing_view_page'            => [
						'url'      => [ $this, 'get_listing_view_url' ],
						'match'    => [ $this, 'is_listing_view_page' ],
						'action'   => [ $this, 'render_listing_view_page' ],

						'redirect' => [
							[
								'callback' => [ $this, 'redirect_listing_view_page' ],
								'_order'   => 5,
							],
						],
					],

					'listings_edit_page'           => [
						'title'     => hivepress()->translator->get_string( 'listings' ),
						'base'      => 'user_account_page',
						'path'      => '/listings',
						'redirect'  => [ $this, 'redirect_listings_edit_page' ],
						'action'    => [ $this, 'render_listings_edit_page' ],
						'paginated' => true,
					],

					'listing_edit_page'            => [
						'base'     => 'listings_edit_page',
						'path'     => '/(?P<listing_id>\d+)',
						'title'    => [ $this, 'get_listing_edit_title' ],
						'redirect' => [ $this, 'redirect_listing_edit_page' ],
						'action'   => [ $this, 'render_listing_edit_page' ],
					],

					'listing_submit_page'          => [
						'path'     => '/submit-listing',
						'redirect' => [ $this, 'redirect_listing_submit_page' ],
					],

					// @deprecated since version 1.3.0.
					'listing/submit_listing'       => [
						'base' => 'listing_submit_page',
					],

					'listing_submit_profile_page'  => [
						'title'    => esc_html_x( 'Complete Profile', 'imperative', 'hivepress' ),
						'base'     => 'listing_submit_page',
						'path'     => '/profile',
						'redirect' => [ $this, 'redirect_listing_submit_profile_page' ],
						'action'   => [ $this, 'render_listing_submit_profile_page' ],
					],

					'listing_submit_category_page' => [
						'title'    => hivepress()->translator->get_string( 'select_category_imperative' ),
						'base'     => 'listing_submit_page',
						'path'     => '/category/?(?P<listing_category_id>\d+)?',
						'redirect' => [ $this, 'redirect_listing_submit_category_page' ],
						'action'   => [ $this, 'render_listing_submit_category_page' ],
					],

					'listing_submit_details_page'  => [
						'title'    => hivepress()->translator->get_string( 'add_details_imperative' ),
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

					'listing_renew_page'           => [
						'base'     => 'listing_edit_page',
						'path'     => '/renew',
						'redirect' => [ $this, 'redirect_listing_renew_page' ],
					],

					'listing_renew_complete_page'  => [
						'title'    => hivepress()->translator->get_string( 'listing_renewed' ),
						'base'     => 'listing_renew_page',
						'path'     => '/complete',
						'redirect' => [ $this, 'redirect_listing_renew_complete_page' ],
						'action'   => [ $this, 'render_listing_renew_complete_page' ],
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
	 * Gets listings.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function get_listings( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return hp\rest_error( 403 );
		}

		// Get search query.
		$query = sanitize_text_field( $request->get_param( 'search' ) );

		if ( strlen( $query ) < 3 ) {
			return hp\rest_error( 400 );
		}

		// Get listings.
		$listings = Models\Listing::query()->filter(
			[
				'status' => 'publish',
			]
		)->search( $query )
		->limit( 20 )
		->get();

		// Get results.
		$results = [];

		if ( $request->get_param( 'context' ) === 'list' ) {
			foreach ( $listings as $listing ) {
				$results[] = [
					'id'   => $listing->get_id(),
					'text' => $listing->get_title(),
				];
			}
		}

		return hp\rest_response( 200, $results );
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
		$form = null;

		if ( $listing->get_status() === 'auto-draft' ) {
			$form = new Forms\Listing_Submit( [ 'model' => $listing ] );
		} else {
			$form = new Forms\Listing_Update( [ 'model' => $listing ] );
		}

		$form->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get attributes.
		$attributes = [];

		if ( $listing->get_status() !== 'auto-draft' ) {
			foreach ( $form->get_fields() as $field ) {
				if ( hp\get_array_value( $field->get_args(), '_moderated' ) ) {
					$value = call_user_func( [ $listing, 'get_' . $field->get_name() ] );

					if ( $field->get_value() !== $value ) {
						$attributes[] = $field->get_label();
					}
				}
			}
		}

		// Set values.
		$listing->fill( $form->get_values() );

		if ( $attributes ) {

			// Set status.
			$listing->set_status( 'pending' );

			// Send email.
			( new Emails\Listing_Update(
				[
					'recipient' => get_option( 'admin_email' ),

					'tokens'    => [
						'listing_title'      => $listing->get_title(),
						'listing_attributes' => implode( ', ', $attributes ),
						'listing_url'        => hivepress()->router->get_admin_url( 'post', $listing->get_id() ),
						'listing'            => $listing,
						'user'               => hivepress()->request->get_user(),
					],
				]
			) )->send();
		}

		if ( ! $listing->save() ) {
			return hp\rest_error( 400, $listing->_get_errors() );
		}

		// Get code.
		$code = 200;

		if ( $attributes ) {
			$code = 307;
		}

		return hp\rest_response(
			$code,
			[
				'id' => $listing->get_id(),
			]
		);
	}

	/**
	 * Hides listing.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function hide_listing( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( $request->get_param( 'listing_id' ) );

		if ( empty( $listing ) ) {
			return hp\rest_error( 404 );
		}

		if ( get_current_user_id() !== $listing->get_user__id() || ! in_array( $listing->get_status(), [ 'draft', 'publish' ], true ) ) {
			return hp\rest_error( 400 );
		}

		if ( $listing->get_status() === 'draft' && $listing->get_expired_time() && $listing->get_expired_time() < time() ) {
			return hp\rest_error( 400 );
		}

		// Update status.
		if ( $listing->get_status() === 'draft' ) {
			$listing->set_status( 'publish' );
		} else {
			$listing->set_status( 'draft' );
		}

		$listing->save_status();

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

		// Check permissions.
		if ( ! get_option( 'hp_listing_enable_reporting', true ) ) {
			return hp\rest_error( 403 );
		}

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
					'listing'        => $listing,
					'user'           => hivepress()->request->get_user(),
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
		if ( ! $listing->trash() ) {
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

		return ( $page_id && is_page( $page_id ) ) || is_post_type_archive( 'hp_listing' ) || ( is_tax() && strpos( get_queried_object()->taxonomy, 'hp_listing_' ) === 0 );
	}

	/**
	 * Redirects listings view page.
	 *
	 * @return mixed
	 */
	public function redirect_listings_view_page() {

		// Get category.
		$category    = null;
		$category_id = is_tax() ? get_queried_object_id() : absint( hp\get_array_value( $_GET, '_category' ) );

		if ( $category_id ) {
			$category = Models\Listing_Category::query()->get_by_id( $category_id );
		}

		// Set request context.
		hivepress()->request->set_context( 'listing_category', $category );

		return false;
	}

	/**
	 * Renders listings view page.
	 *
	 * @return string
	 */
	public function render_listings_view_page() {

		// Get category.
		$category = hivepress()->request->get_context( 'listing_category' );

		if ( ( ( is_page() || ( empty( $category ) && is_post_type_archive() ) ) && get_option( 'hp_page_listings_display_categories' ) ) || ( $category && get_term_meta( $category->get_id(), 'hp_display_subcategories', true ) ) ) {

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

				// Get featured IDs.
				if ( get_option( 'hp_listings_featured_per_page' ) ) {
					hivepress()->request->set_context(
						'featured_ids',
						Models\Listing::query()->filter(
							[
								'status'   => 'publish',
								'featured' => true,
							]
						)->order( 'random' )
						->limit( get_option( 'hp_listings_featured_per_page' ) )
						->get_ids()
					);
				}

				// Query listings.
				hivepress()->request->set_context(
					'post_query',
					Models\Listing::query()->filter(
						[
							'status'     => 'publish',
							'id__not_in' => hivepress()->request->get_context( 'featured_ids', [] ),
						]
					)->order( [ 'created_date' => 'desc' ] )
					->limit( get_option( 'hp_listings_per_page' ) )
					->paginate( hivepress()->request->get_page_number() )
					->get_args()
				);
			}

			// Render listings.
			return ( new Blocks\Template(
				[
					'template' => 'listings_view_page',

					'context'  => [
						'listing_category' => $category,
						'listings'         => [],
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
	 * Redirects listing view page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_view_page() {
		the_post();

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( get_post() );

		// @todo replace temporary fix.
		$listing->get_images__id();

		// Get vendor.
		$vendor = $listing->get_vendor();

		// Set request context.
		hivepress()->request->set_context( 'listing', $listing );
		hivepress()->request->set_context( 'vendor', $vendor );

		return false;
	}

	/**
	 * Renders listing view page.
	 *
	 * @return string
	 */
	public function render_listing_view_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listing_view_page',

				'context'  => [
					'listing' => hivepress()->request->get_context( 'listing' ),
					'vendor'  => hivepress()->request->get_context( 'vendor' ),
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
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check listings.
		if ( ! hivepress()->request->get_context( 'listing_count' ) ) {
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
		hivepress()->request->set_context(
			'post_query',
			Models\Listing::query()->filter(
				[
					'status__in' => [ 'draft', 'pending', 'publish' ],
					'user'       => get_current_user_id(),
				]
			)->order( [ 'created_date' => 'desc' ] )
			->limit( 20 )
			->paginate( hivepress()->request->get_page_number() )
			->get_args()
		);

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'listings_edit_page',

				'context'  => [
					'listings' => [],
				],
			]
		) )->render();
	}

	/**
	 * Gets listing edit title.
	 *
	 * @return string
	 */
	public function get_listing_edit_title() {
		$title = null;

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( hivepress()->request->get_param( 'listing_id' ) );

		// Set title.
		if ( $listing ) {
			$title = $listing->get_title();
		}

		// Set request context.
		hivepress()->request->set_context( 'listing', $listing );

		return $title;
	}

	/**
	 * Redirects listing edit page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_edit_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check listing.
		$listing = hivepress()->request->get_context( 'listing' );

		if ( empty( $listing ) || get_current_user_id() !== $listing->get_user__id() || ! in_array( $listing->get_status(), [ 'draft', 'publish' ], true ) ) {
			return hivepress()->router->get_url( 'listings_edit_page' );
		}

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

		// Check permissions.
		if ( ! get_option( 'hp_listing_enable_submission' ) ) {
			return home_url();
		}

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Get listing.
		$listing = Models\Listing::query()->filter(
			[
				'status'  => 'auto-draft',
				'drafted' => true,
				'user'    => get_current_user_id(),
			]
		)->get_first();

		if ( empty( $listing ) ) {

			// Add listing.
			$listing = ( new Models\Listing() )->fill(
				[
					'status'  => 'auto-draft',
					'drafted' => true,
					'user'    => get_current_user_id(),
				]
			);

			if ( ! $listing->save( [ 'status', 'drafted', 'user' ] ) ) {
				return home_url();
			}
		}

		// Set request context.
		hivepress()->request->set_context( 'listing', $listing );

		return true;
	}

	/**
	 * Redirects listing submit profile page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_submit_profile_page() {

		// Get vendor.
		$vendor = Models\Vendor::query()->filter(
			[
				'status' => [ 'auto-draft', 'draft', 'publish' ],
				'user'   => get_current_user_id(),
			]
		)->get_first();

		if ( ! $vendor ) {

			// Get user.
			$user = hivepress()->request->get_context( 'user' );

			// Add vendor.
			$vendor = ( new Models\Vendor() )->fill(
				[
					'name'        => $user->get_display_name(),
					'description' => $user->get_description(),
					'slug'        => $user->get_username(),
					'status'      => 'auto-draft',
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
				return home_url();
			}
		}

		// Set request context.
		hivepress()->request->set_context( 'vendor', $vendor );

		// Check vendor.
		if ( $vendor->validate() ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders listing submit profile page.
	 *
	 * @return string
	 */
	public function render_listing_submit_profile_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listing_submit_profile_page',

				'context'  => [
					'vendor' => hivepress()->request->get_context( 'vendor' ),
					'user'   => hivepress()->request->get_context( 'user' ),
				],
			]
		) )->render();
	}

	/**
	 * Redirects listing submit category page.
	 *
	 * @deprecated since version 1.6.4.
	 * @return mixed
	 */
	public function redirect_listing_submit_category_page() {

		// Redirect page.
		if ( ! has_filter( 'hivepress/v1/templates/listing_submit_category_page' ) && ! has_filter( 'hivepress/v1/templates/listing_submit_category_page/blocks' ) ) {
			return true;
		}

		// Check categories.
		if ( ! Models\Listing_Category::query()->get_first_id() ) {
			return true;
		}

		// Get listing.
		$listing = hivepress()->request->get_context( 'listing' );

		if ( hivepress()->request->get_param( 'listing_category_id' ) ) {

			// Get category.
			$category = Models\Listing_Category::query()->get_by_id( hivepress()->request->get_param( 'listing_category_id' ) );

			if ( empty( $category ) ) {
				return hivepress()->router->get_url( 'listing_submit_category_page' );
			}

			if ( ! $category->get_children__id() ) {

				// Set listing category.
				$listing->set_categories( $category->get_id() )->save_categories();

				return true;
			}

			// Set request context.
			hivepress()->request->set_context( 'listing_category', $category );
		}

		// Check category.
		if ( $listing->get_categories__id() ) {
			return;
		}

		return false;
	}

	/**
	 * Renders listing submit category page.
	 *
	 * @deprecated since version 1.6.4.
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

		// @todo replace temporary fix.
		$listing->get_images__id();

		// Check redirect.
		// @todo remove temporary fix.
		if ( isset( $_GET['redirect'] ) ) {
			wp_set_post_terms( $listing->get_id(), [], 'hp_listing_category' );

			return hivepress()->router->get_url( 'listing_submit_details_page' );
		}

		// Check listing.
		if ( $listing->validate() ) {
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
		$listing->fill(
			[
				'status'  => $status,
				'drafted' => null,
			]
		)->save( [ 'status', 'drafted' ] );

		// Get vendor.
		$vendor = hivepress()->request->get_context( 'vendor' );

		// Update vendor.
		if ( 'pending' === $status && $vendor->get_status() === 'auto-draft' ) {
			$vendor->set_status( 'draft' )->save_status();
		}

		// Send email.
		( new Emails\Listing_Submit(
			[
				'recipient' => get_option( 'admin_email' ),

				'tokens'    => [
					'listing_title' => $listing->get_title(),
					'listing_url'   => 'publish' === $status ? get_permalink( $listing->get_id() ) : get_preview_post_link( $listing->get_id() ),
					'listing'       => $listing,
					'user'          => hivepress()->request->get_user(),
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
	 * Redirects listing renew page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_renew_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Get listing.
		$listing = Models\Listing::query()->get_by_id( hivepress()->request->get_param( 'listing_id' ) );

		if ( empty( $listing ) || get_current_user_id() !== $listing->get_user__id() || $listing->get_status() !== 'draft' || ! $listing->get_expired_time() || $listing->get_expired_time() > time() ) {
			return home_url();
		}

		// Set request context.
		hivepress()->request->set_context( 'listing', $listing );

		return true;
	}

	/**
	 * Redirects listing renew complete page.
	 *
	 * @return mixed
	 */
	public function redirect_listing_renew_complete_page() {

		// Get listing.
		$listing = hivepress()->request->get_context( 'listing' );

		// Get date.
		$date = current_time( 'mysql' );

		// Update listing.
		$listing->fill(
			[
				'status'           => 'publish',
				'created_date'     => $date,
				'created_date_gmt' => get_gmt_from_date( $date ),
				'expired_time'     => null,
			]
		)->save(
			[
				'status',
				'created_date',
				'created_date_gmt',
				'expired_time',
			]
		);

		return false;
	}

	/**
	 * Renders listing renew complete page.
	 *
	 * @return string
	 */
	public function render_listing_renew_complete_page() {
		return ( new Blocks\Template(
			[
				'template' => 'listing_renew_complete_page',

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
