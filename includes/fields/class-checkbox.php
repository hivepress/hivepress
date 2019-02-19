<?php
/**
 * Checkbox field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Checkbox field class.
 *
 * @class Checkbox
 */
class Checkbox extends Field {

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		// todo.
	}

	/**
	 * Validate field value.
	 */
	public function validate() {
		parent::validate();

		// todo.
		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
	}
}
