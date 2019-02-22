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

	/**
	 * Maximum length.
	 *
	 * @var int
	 */
	protected $max_length = 254;

	// Forbid setting maximum length.
	final private function set_max_length() {}

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
			$this->errors[] = hp_sanitize_html( __( '%s should be a valid email address.', 'hivepress' ), '<strong>' . $this->get_label() . '</strong>' );
		}

		return empty( $this->errors );
	}
}
