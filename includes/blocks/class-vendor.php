<?php
/**
 * Vendor block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor block class.
 *
 * @class Vendor
 */
class Vendor extends Template {

	/**
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		global $post;

		$output = '';

		if ( is_singular( 'hp_listing' ) ) {

			// Get vendor.
			$vendor = Models\Vendor::get( wp_get_post_parent_id( get_the_ID() ) );

			if ( ! is_null( $vendor ) ) {

				// Set query.
				$post = get_post( $vendor->get_id() );

				setup_postdata( $post );

				// Set vendor.
				$this->context['vendor'] = $vendor;

				// Render vendor.
				$output .= parent::render();

				// Reset query.
				wp_reset_postdata();
			}
		}

		return $output;
	}
}
