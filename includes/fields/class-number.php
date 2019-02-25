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
	 * Gets field attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {

		// Set step.
		$this->attributes['step'] = 1 / pow( 10, $this->get_decimals() );

		// Set minimum value.
		if ( ! is_null( $this->min_value ) ) {
			$this->attributes['min'] = $this->get_min_value();
		}

		// Set maximum value.
		if ( ! is_null( $this->max_value ) ) {
			$this->attributes['max'] = $this->get_max_value();
		}

		return $this->attributes;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = round( floatval( $this->value ), $this->decimals );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			if ( ! is_null( $this->min_value ) && $this->value < $this->min_value ) {
				$this->errors[] = sprintf( esc_html__( "%1\$s can't be lower than %2\$s.", 'hivepress' ), $this->get_label(), number_format_i18n( $this->min_value ) );
			}

			if ( ! is_null( $this->max_value ) && $this->value > $this->max_value ) {
				$this->errors[] = sprintf( esc_html__( "%1\$s can't be greater than %2\$s.", 'hivepress' ), $this->get_label(), number_format_i18n( $this->min_value ) );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<input type="' . esc_attr( $this->get_type() ) . '" name="' . esc_attr( $this->get_name() ) . '" value="' . esc_attr( $this->get_value() ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';
	}
}
