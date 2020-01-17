<?php
/**
 * Vendor controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor controller class.
 *
 * @class Vendor
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
					'vendor_view_page' => [
						'match'  => [ $this, 'is_vendor_view_page' ],
						'url'    => [ $this, 'get_vendor_view_url' ],
						'title'  => [ $this, 'get_vendor_view_title' ],
						'action' => [ $this, 'render_vendor_view_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
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
		query_posts(
			Models\Listing::query()->filter(
				[
					'status'     => 'publish',
					'vendor'     => $vendor->get_id(),
					'id__not_in' => hivepress()->request->get_context( 'featured_ids', [] ),
				]
			)->order( [ 'created_date' => 'desc' ] )
			->limit( get_option( 'hp_listings_per_page' ) )
			->paginate( hivepress()->request->get_context( 'page_number' ) )
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
}
