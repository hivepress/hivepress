<?php
/**
 * Radio field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

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
		$args['multiple'] = false;

		parent::__construct( $args );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->options as $value => $label ) {
			$output .= '<label for="' . esc_attr( $this->name . '_' . $value ) . '"><input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $this->name . '_' . $value ) . '" value="' . esc_attr( $value ) . '" ' . checked( $this->value, $value, false ) . '><span>' . esc_html( $label ) . '</span></label>';
		}

		$output .= '</div>';

		return $output;
	}
}
