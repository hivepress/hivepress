<?php
/**
 * Checkboxes field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

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
		$args['multiple'] = true;

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
			$output .= ( new Checkbox(
				[
					'name'    => $this->name . '_' . $value,
					'caption' => $label,
				]
			) )->render();
		}

		$output .= '</div>';

		return $output;
	}
}
