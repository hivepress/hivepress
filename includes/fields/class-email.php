<?php
/**
 * Email field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Email field class.
 *
 * @class Email
 */
class Email extends Text {

	// todo set max length to 254.

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_email( $this->value );
		}
	}

	/**
	 * Validate field value.
	 */
	public function validate() {
		parent::validate();

		if ( ! is_null( $this->value ) && ! is_email( $this->value ) ) {
			$this->errors[] = 'todo';
		}

		return empty( $this->errors );
	}
}
