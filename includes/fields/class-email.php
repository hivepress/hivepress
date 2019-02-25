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
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Set maximum length.
		$this->max_length = 254;

		parent::__construct( $args );
	}

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
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && ! is_email( $this->value ) ) {
			$this->errors[] = sprintf( esc_html__( '%s should be a valid email address.', 'hivepress' ), $this->get_label() );
		}

		return empty( $this->errors );
	}
}
