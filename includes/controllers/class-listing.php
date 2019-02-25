<?php
/**
 * Listing controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing controller class.
 *
 * @class Listing
 */
class Listing extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
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
								'path'    => '/(?P<id>\d+)',
								'methods' => 'DELETE',
								'action'  => 'delete_listing',
							],
						],
					],

					[
						'rule'   => 'is_listing_page',
						'action' => 'render_listing_page',
					],

					[
						'rule'   => 'is_listings_page',
						'action' => 'render_listings_page',
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
	 * @return mixed
	 */
	public function update_listing( $request ) {
		// todo.
	}

	/**
	 * Deletes listing.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function delete_listing( $request ) {
		// todo.
	}

	/**
	 * Checks listing page.
	 *
	 * @return bool
	 */
	public function is_listing_page() {
		return is_singular( 'hp_listing' );
	}

	/**
	 * Renders listing page.
	 *
	 * @return string
	 */
	public function render_listing_page() {
		// todo.
		$output = '';

		ob_start();
		get_header();
		$output .= ob_get_contents();
		ob_end_clean();

		$template = hivepress()->get_config( 'templates' )['listing'];

		foreach ( $template['blocks'] as $block_name => $block ) {
			$block_class = '\HivePress\Blocks\\' . $block['type'];

			$output .= ( new $block_class( $block ) )->render();
		}

		ob_start();
		get_footer();
		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Checks listings page.
	 *
	 * @return bool
	 */
	public function is_listings_page() {
		return is_page( absint( get_option( 'hp_page_listings' ) ) ) || is_post_type_archive( 'hp_listing' ) || is_tax( get_object_taxonomies( 'hp_listing' ) );
	}

	/**
	 * Renders listings page.
	 *
	 * @return string
	 */
	public function render_listings_page() {
		// todo.
		$output = '';

		ob_start();
		get_header();
		$output .= ob_get_contents();
		ob_end_clean();

		$template = hivepress()->get_config( 'templates' )['listings'];

		foreach ( $template['blocks'] as $block_name => $block ) {
			$block_class = '\HivePress\Blocks\\' . $block['type'];

			$output .= ( new $block_class( $block ) )->render();
		}

		ob_start();
		get_footer();
		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}
}
