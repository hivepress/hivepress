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
	 * @param array $props Field properties.
	 */
	public function __construct( $props ) {
		parent::__construct( $props );

		// Set maximum length.
		$this->set_max_length( 254 );
	}

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
