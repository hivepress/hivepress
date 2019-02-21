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
		return '<label for="' . esc_attr( $this->get_name() ) . '"><input type="' . esc_attr( $this->get_type() ) . '" name="' . esc_attr( $this->get_name() ) . '" id="' . esc_attr( $this->get_name() ) . '" value="' . esc_attr( $this->get_value() ) . '" ' . checked( $this->get_value(), true, false ) . ' ' . hp_html_attributes( $this->get_attributes() ) . '><span>' . hp_sanitize_html( $this->get_caption() ) . '</span></label>';
	}
}
