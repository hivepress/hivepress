<?php
/**
 * Vendor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor component class.
 *
 * @class Vendor
 */
final class Vendor {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( ! is_admin() ) {

			// Disable redirect.
			add_action( 'template_redirect', [ $this, 'disable_page_redirect' ], 1 );

			// Set page title.
			add_filter( 'hivepress/v1/controllers/vendor/routes/view_vendor', [ $this, 'set_page_title' ] );
		}
	}

	/**
	 * Disables page redirect.
	 */
	public function disable_page_redirect() {
		if ( is_singular( 'hp_vendor' ) ) {
			remove_action( 'template_redirect', 'redirect_canonical' );
		}
	}

	/**
	 * Sets page title.
	 *
	 * @param array $route Route arguments.
	 * @return array
	 */
	public function set_page_title( $route ) {
		return array_merge( $route, [ 'title' => sprintf( esc_html__( 'Listings by %s', 'hivepress' ), get_the_title() ) ] );
	}
}
