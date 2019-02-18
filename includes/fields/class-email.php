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
	protected function validate() {
		if ( ! is_email( $value ) ) {
			$this->errors[] = 'todo';
		}

		return parent::validate();
	}
}
