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
class Vendor extends Controller {

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
						'url'    => [ $this, 'get_vendor_view_url' ],
						'match'  => [ $this, 'is_vendor_view_page' ],
						'action' => [ $this, 'render_vendor_view_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
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
	 * Matches vendor view URL.
	 *
	 * @return bool
	 */
	public function is_vendor_view_page() {
		return is_singular( 'hp_vendor' );
	}

	/**
	 * Renders vendor view page.
	 *
	 * @return string
	 */
	public function render_vendor_view_page() {
		the_post();

		// Get vendor.
		$vendor = Models\Vendor::query()->get_by_id( get_post() );

		// Query listings.
		query_posts(
			Models\Listing::query()->filter(
				[
					'status'    => 'publish',
					'vendor_id' => $vendor->get_id(),
				]
			)->limit( get_option( 'hp_listings_per_page' ) )
			->paginate( hp\get_current_page() )
			->get_args()
		);

		// Render template.
		return ( new Blocks\Template(
			[
				'template' => 'vendor_view_page',

				'context'  => [
					'vendor' => $vendor,
				],
			]
		) )->render();
	}
}
