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

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Set multiple property.
		$this->multiple = true;

		parent::__construct( $args );
	}

	// Forbid setting multiple status.
	final private function set_multiple() {}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp_html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->get_options() as $option_value => $option_label ) {
			$output .= ( new Checkbox(
				[
					'name'    => $this->get_name() . '_' . $option_value,
					'caption' => $option_label,
				]
			) )->render();
		}

		$output .= '</div>';

		return $output;
	}
}
