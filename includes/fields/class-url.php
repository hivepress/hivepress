<?php
/**
 * URL field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * URL field class.
 *
 * @class URL
 */
class URL extends Text {

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = esc_url_raw( $this->value );
		}
	}
}
