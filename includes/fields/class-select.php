<?php
/**
 * Select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Select field class.
 *
 * @class Select
 */
class Select extends Field {

	/**
	 * Multiple property.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Field options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Sets multiple property.
	 *
	 * @param bool $multiple Multiple property.
	 */
	final protected function set_multiple( $multiple ) {
		$this->multiple = boolval( $multiple );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && ! in_array( $this->value, array_keys( $this->options ), true ) ) {
			$this->add_errors( [ sprintf( esc_html__( '%s is invalid', 'hivepress' ), $this->label ) ] );
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<select name="' . esc_attr( $this->name ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->options as $option_value => $option_label ) {
			$output .= '<option value="' . esc_attr( $option_value ) . '" ' . selected( $this->value, $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
