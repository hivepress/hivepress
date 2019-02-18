<?php
/**
 * Number field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Number field class.
 *
 * @class Number
 */
class Number extends Field {

	/**
	 * Decimals number.
	 *
	 * @var int
	 */
	protected $decimals = 0;

	/**
	 * Minimum value.
	 *
	 * @var int
	 */
	protected $min_value;

	/**
	 * Maximum value.
	 *
	 * @var int
	 */
	protected $max_value;

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = round( floatval( $this->value ), $this->decimals );
		}
	}

	/**
	 * Validate field value.
	 */
	public function validate() {
		if ( ! is_null( $this->min_value ) && $this->value < $this->min_value ) {
			$this->errors[] = 'todo';
		}

		if ( ! is_null( $this->max_value ) && $this->value > $this->max_value ) {
			$this->errors[] = 'todo';
		}

		return parent::validate();
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Get step.
		$step = 1 / pow( 10, $this->decimals );

		return '<input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" step="' . esc_attr( $step ) . '" min="' . esc_attr( $this->min_value ) . '" max="' . esc_attr( $this->max_value ) . '" ' . hp_html_attributes( $this->attributes ) . '>';
	}
}
