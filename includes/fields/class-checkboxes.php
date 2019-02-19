<?php
/**
 * Checkboxes field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Checkboxes field class.
 *
 * @class Checkboxes
 */
class Checkboxes extends Select {

	// todo set multiple to true.

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp_html_attributes( $this->attributes ) . '>';

		foreach ( $this->options as $option_value => $option_label ) {
			// todo.
		}

		$output .= '</div>';

		return $output;
	}
}
