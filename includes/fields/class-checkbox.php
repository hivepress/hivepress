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
	 * Checkbox caption.
	 *
	 * @var string
	 */
	protected $caption;

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
		return '<label for="' . esc_attr( $this->name ) . '"><input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . checked( $this->value, true, false ) . ' ' . hp_html_attributes( $this->attributes ) . '><span>' . hp_sanitize_html( $this->caption ) . '</span></label>';
	}
}
