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
	 * Matches controller URL.
	 *
	 * @return bool
	 */
	public function match() {
		return is_singular( 'hp_listing' );
	}

	/**
	 * Renders controller response.
	 *
	 * @return string
	 */
	public function render() {
		//todo.
		$output = '';

		$template = hivepress()->get_config( 'templates' )['listing'];

		foreach ( $template['blocks'] as $block_name => $block ) {
			$block_class = '\HivePress\Blocks\\' . $block['type'];

			$output .= ( new $block_class( $block ) )->render();
		}

		return $output;
	}
}
