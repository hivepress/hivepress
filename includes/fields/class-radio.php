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
		$output = '<div ' . hp_html_attributes( $this->attributes ) . '>';

		foreach ( $this->options as $option_value => $option_label ) {
			$output .= '<label for="' . esc_attr( $this->name . '_' . $option_value ) . '"><input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $this->name . '_' . $option_value ) . '" value="' . esc_attr( $option_value ) . '" ' . checked( $value, $option_value, false ) . ' ' . hp_html_attributes( $this->attributes ) . '><span>' . esc_html( $option_label ) . '</span></label>';
		}

		$output .= '</div>';

		return $output;
	}
}
