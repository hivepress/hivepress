<?php
/**
 * Textarea field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Textarea field class.
 *
 * @class Textarea
 */
class Textarea extends Text {

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_textarea_field( $this->value );
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<textarea name="' . esc_attr( $this->get_name() ) . '" minlength="' . esc_attr( $this->get_min_length() ) . '" maxlength="' . esc_attr( $this->get_max_length() ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>' . esc_textarea( $this->get_value() ) . '</textarea>';
	}
}
