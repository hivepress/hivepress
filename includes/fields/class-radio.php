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

	// todo set multiple to false.

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp_html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->get_options() as $option_value => $option_label ) {
			$output .= '<label for="' . esc_attr( $this->get_name() . '_' . $option_value ) . '"><input type="' . esc_attr( $this->get_type() ) . '" name="' . esc_attr( $this->get_name() ) . '" id="' . esc_attr( $this->get_name() . '_' . $option_value ) . '" value="' . esc_attr( $option_value ) . '" ' . checked( $this->get_value(), $option_value, false ) . ' ' . hp_html_attributes( $this->get_attributes() ) . '><span>' . esc_html( $option_label ) . '</span></label>';
		}

		$output .= '</div>';

		return $output;
	}
}
