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
			add_action( 'template_redirect', [ $this, 'disable_redirect' ], 1 );

			// Set title.
			add_filter( 'document_title_parts', [ $this, 'set_title' ] );
		}
	}

	/**
	 * Disables redirect.
	 */
	public function disable_redirect() {
		if ( is_singular( 'hp_vendor' ) ) {
			remove_action( 'template_redirect', 'redirect_canonical' );
		}
	}

	/**
	 * Sets title.
	 *
	 * @param array $parts Title parts.
	 * @return string
	 */
	public function set_title( $parts ) {
		// todo.
		return $parts;
	}
}
