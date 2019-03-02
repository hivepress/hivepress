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
	 * Sets field caption.
	 *
	 * @param string $caption Field caption.
	 */
	final protected function set_caption( $caption ) {
		$this->caption = $caption;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = boolval( $this->value );
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<label for="' . esc_attr( $this->name ) . '"><input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . checked( $this->value, true, false ) . ' ' . hp_html_attributes( $this->get_attributes() ) . '><span>' . hp_sanitize_html( $this->caption ) . '</span></label>';
	}
}
