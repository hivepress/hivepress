<?php
/**
 * File upload field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File upload field class.
 *
 * @class File_Upload
 */
class File_Upload extends Field {

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		// todo.
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp_html_attributes( $this->attributes ) . '>';

		// todo.
		$output .= '</div>';

		return $output;
	}
}
