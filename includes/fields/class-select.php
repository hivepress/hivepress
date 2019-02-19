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
	 * Field options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Validate field value.
	 */
	public function validate() {
		parent::validate();

		if ( ! is_null( $this->value ) && ! in_array( $this->value, array_keys( $this->options ), true ) ) {
			$this->errors[] = 'todo';
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<select name="' . esc_attr( $this->name ) . '">';

		foreach ( $this->options as $option_value => $option_label ) {
			$output .= '<option value="' . esc_attr( $option_value ) . '" ' . selected( $this->value, $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
