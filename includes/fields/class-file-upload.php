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

	// todo.
	public function validate() {
		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$output = '<div ' . hp_html_attributes( $this->get_attributes() ) . '>';

		$output .= '<label for="' . esc_attr( $this->get_name() ) . '">';

		$output .= '<button type="button">' . esc_html__( 'Select File', 'hivepress' ) . '</button>';

		$output .= ( new File(
			[
				'name'       => $this->get_name(),
				'type'       => 'file',
				'attributes' => [
					'class' => 'hp-js-file-upload',
				],
			]
		) )->render();

		$output .= '</label>';

		$output .= '</div>';

		return $output;
	}
}
