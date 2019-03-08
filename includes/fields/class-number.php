<?php
/**
 * Number field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

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
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title' => esc_html__( 'Number', 'hivepress' ),
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Gets field attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		$attributes = [];

		// Set step.
		$attributes['step'] = 1 / pow( 10, $this->decimals );

		// Set minimum value.
		if ( ! is_null( $this->min_value ) ) {
			$attributes['min'] = $this->min_value;
		}

		// Set maximum value.
		if ( ! is_null( $this->max_value ) ) {
			$attributes['max'] = $this->max_value;
		}

		return hp\merge_arrays( parent::get_attributes(), $attributes );
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
				$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be lower than %2\$s", 'hivepress' ), $this->label, number_format_i18n( $this->min_value ) ) ] );
			}

			if ( ! is_null( $this->max_value ) && $this->value > $this->max_value ) {
				$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be greater than %2\$s", 'hivepress' ), $this->label, number_format_i18n( $this->max_value ) ) ] );
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
		return '<input type="' . esc_attr( self::$type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->get_attributes() ) . '>';
	}
}
