<?php
/**
 * Listings controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings controller class.
 *
 * @class Listings
 */
class Listings extends Controller {

	/**
	 * Matches controller URL.
	 *
	 * @return bool
	 */
	public function match() {
		return is_page( absint( get_option( 'hp_page_listings' ) ) ) || is_post_type_archive( 'hp_listing' ) || is_tax( get_object_taxonomies( 'hp_listing' ) );
	}

	/**
	 * Renders controller response.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$output = '';

		$template = hivepress()->get_config( 'templates' )['listings'];

		foreach ( $template['blocks'] as $block_name => $block ) {
			$block_class = '\HivePress\Blocks\\' . $block['type'];

			$output .= ( new $block_class( $block ) )->render();
		}

		return $output;
	}
}
