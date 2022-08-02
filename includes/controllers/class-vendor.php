<?php
/**
 * Vendor controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Emails;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages vendors.
 */
final class Vendor extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'vendors_resource'              => [
						'path'   => '/vendors',
						'method' => 'GET',
						'action' => [ $this, 'get_vendors' ],
						'rest'   => true,
					],

					'vendor_account_page'           => [
						'base' => 'user_account_page',
						'path' => '/vendor',
					],

					'vendors_view_page'             => [
						'url'      => [ $this, 'get_vendors_view_url' ],
						'match'    => [ $this, 'is_vendors_view_page' ],
						'action'   => [ $this, 'render_vendors_view_page' ],

						'redirect' => [
							[
								'callback' => [ $this, 'redirect_vendors_view_page' ],
								'_order'   => 5,
							],
						],
					],

					'vendor_view_page'              => [
						'match'  => [ $this, 'is_vendor_view_page' ],
						'url'    => [ $this, 'get_vendor_view_url' ],
						'title'  => [ $this, 'get_vendor_view_title' ],
						'action' => [ $this, 'render_vendor_view_page' ],
					],

					'vendor_register_page'          => [
						'path'     => '/register-vendor',
						'redirect' => [ $this, 'redirect_vendor_register_page' ],
					],

					'vendor_register_profile_page'  => [
						'title'    => esc_html_x( 'Complete Profile', 'imperative', 'hivepress' ),
						'base'     => 'vendor_register_page',
						'path'     => '/profile',
						'redirect' => [ $this, 'redirect_vendor_register_profile_page' ],
						'action'   => [ $this, 'render_vendor_register_profile_page' ],
					],

					'vendor_register_complete_page' => [
						'base'     => 'vendor_register_page',
						'path'     => '/complete',
						'redirect' => [ $this, 'redirect_vendor_register_complete_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets vendors.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function get_vendors( $request ) {

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

		// Get vendors.
		$vendors = Models\Vendor::query()->filter(
			[
				'status' => 'publish',
			]
		)->search( $query )
		->limit( 20 )
		->get();

		// Get results.
		$results = [];

		if ( $request->get_param( 'context' ) === 'list' ) {
			foreach ( $vendors as $vendor ) {
				$results[] = [
					'id'   => $vendor->get_id(),
					'text' => $vendor->get_name(),
				];
			}
		}

		return hp\rest_response( 200, $results );
	}

	/**
	 * Gets vendors view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_vendors_view_url( $params ) {
		return get_post_type_archive_link( 'hp_vendor' );
	}

	/**
	 * Matches vendors view URL.
	 *
	 * @return bool
	 */
	public function is_vendors_view_page() {

		// Get page ID.
		$page_id = absint( get_option( 'hp_page_vendors' ) );

		return ( $page_id && is_page( $page_id ) ) || is_post_type_archive( 'hp_vendor' ) || ( is_tax() && strpos( get_queried_object()->taxonomy, 'hp_vendor_' ) === 0 );
	}

	/**
	 * Redirects vendors view page.
	 *
	 * @return mixed
	 */
	public function redirect_vendors_view_page() {
		if ( ! get_option( 'hp_vendor_enable_display' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders vendors view page.
	 *
	 * @return string
	 */
	public function render_vendors_view_page() {
		if ( is_page() ) {

			// Query vendors.
			hivepress()->request->set_context(
				'post_query',
				Models\Vendor::query()->filter(
					[
						'status' => 'publish',
					]
				)->order( [ 'registered_date' => 'desc' ] )
				->limit( get_option( 'hp_vendors_per_page' ) )
				->paginate( hivepress()->request->get_page_number() )
				->get_args()
			);
		}

		// Render vendors.
		return ( new Blocks\Template(
			[
				'template' => 'vendors_view_page',

				'context'  => [
					'vendors' => [],
				],
			]
		) )->render();
	}

	/**
	 * Matches vendor view URL.
	 *
	 * @return bool
	 */
	public function is_vendor_view_page() {
		return is_singular( 'hp_vendor' );
	}

	/**
	 * Gets vendor view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_vendor_view_url( $params ) {
		return get_permalink( hp\get_array_value( $params, 'vendor_id' ) );
	}

	/**
	 * Gets vendor view title.
	 *
	 * @return string
	 */
	public function get_vendor_view_title() {
		the_post();

		// Get vendor.
		$vendor = Models\Vendor::query()->get_by_id( get_post() );

		// Set request context.
		hivepress()->request->set_context( 'vendor', $vendor );

		return sprintf( hivepress()->translator->get_string( 'listings_by_vendor' ), $vendor->get_name() );
	}

	/**
	 * Renders vendor view page.
	 *
	 * @return string
	 */
	public function render_vendor_view_page() {

		// Get vendor.
		$vendor = hivepress()->request->get_context( 'vendor' );

		// Get featured IDs.
		if ( get_option( 'hp_listings_featured_per_page' ) ) {
			hivepress()->request->set_context(
				'featured_ids',
				Models\Listing::query()->filter(
					[
						'status'   => 'publish',
						'vendor'   => $vendor->get_id(),
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
					'vendor'     => $vendor->get_id(),
					'id__not_in' => hivepress()->request->get_context( 'featured_ids', [] ),
				]
			)->order( [ 'created_date' => 'desc' ] )
			->limit( get_option( 'hp_listings_per_page' ) )
			->paginate( hivepress()->request->get_page_number() )
			->get_args()
		);

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'vendor_view_page',

				'context'  => [
					'vendor'   => $vendor,
					'listings' => [],
				],
			]
		) )->render();
	}

	/**
	 * Redirects vendor register page.
	 *
	 * @return mixed
	 */
	public function redirect_vendor_register_page() {

		// Check permissions.
		if ( ! get_option( 'hp_vendor_enable_registration' ) ) {
			return home_url();
		}

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

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
		} elseif ( $vendor->get_status() === 'publish' ) {
			return home_url();
		}

		// Set request context.
		hivepress()->request->set_context( 'vendor', $vendor );

		return true;
	}

	/**
	 * Redirects vendor register profile page.
	 *
	 * @return mixed
	 */
	public function redirect_vendor_register_profile_page() {

		// Get vendor.
		$vendor = hivepress()->request->get_context( 'vendor' );

		// Check vendor.
		if ( $vendor->validate() ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders vendor register profile page.
	 *
	 * @return string
	 */
	public function render_vendor_register_profile_page() {
		return ( new Blocks\Template(
			[
				'template' => 'vendor_register_profile_page',

				'context'  => [
					'vendor' => hivepress()->request->get_context( 'vendor' ),
					'user'   => hivepress()->request->get_context( 'user' ),
				],
			]
		) )->render();
	}

	/**
	 * Redirects vendor register complete page.
	 *
	 * @return mixed
	 */
	public function redirect_vendor_register_complete_page() {

		// Get vendor.
		$vendor = hivepress()->request->get_context( 'vendor' );

		// Update vendor.
		$vendor->set_status( 'publish' )->save_status();

		// Send email.
		( new Emails\Vendor_Register(
			[
				'recipient' => get_option( 'admin_email' ),

				'tokens'    => [
					'vendor_url' => get_permalink( $vendor->get_id() ),
					'vendor'     => $vendor,
					'user'       => hivepress()->request->get_user(),
				],
			]
		) )->send();

		return get_permalink( $vendor->get_id() );
	}
}
