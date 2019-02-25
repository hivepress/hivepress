<?php
/**
 * Radio field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Radio field class.
 *
 * @class Radio
 */
class Radio extends Select {

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Set multiple property.
		$this->multiple = false;

		parent::__construct( $args );
	}

	// Forbid setting multiple property.
	final private function set_multiple() {}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp_html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->get_options() as $option_value => $option_label ) {
			$output .= '<label for="' . esc_attr( $this->get_name() . '_' . $option_value ) . '"><input type="' . esc_attr( $this->get_type() ) . '" name="' . esc_attr( $this->get_name() ) . '" id="' . esc_attr( $this->get_name() . '_' . $option_value ) . '" value="' . esc_attr( $option_value ) . '" ' . checked( $this->get_value(), $option_value, false ) . '><span>' . esc_html( $option_label ) . '</span></label>';
		}

		$output .= '</div>';

		return $output;
	}
}
