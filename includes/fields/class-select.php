<?php
/**
 * Select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Select field class.
 *
 * @class Select
 */
class Select extends Field {

	/**
	 * Select options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Multiple property.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title' => esc_html__( 'Select', 'hivepress' ),
			],
			$args
		);

		parent::init( $args );
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
		$output = '<select name="' . esc_attr( $this->name ) . '" ' . hp\html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->options as $value => $label ) {
			$output .= '<option value="' . esc_attr( $value ) . '" ' . selected( $this->value, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
