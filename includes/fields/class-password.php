<?php
/**
 * Password field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Password field class.
 *
 * @class Password
 */
class Password extends Text {

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = wp_strip_all_tags( $this->value, true );
		}
	}
}
