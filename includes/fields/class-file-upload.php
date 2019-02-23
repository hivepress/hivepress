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
	 * Multiple status.
	 *
	 * @var bool
	 */
	protected $multiple;

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		// todo.
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		parent::validate();

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp_html_attributes( $this->get_attributes() ) . '>';

		// Render files.
		$output .= '<div class="hp-row ' . esc_attr( $this->multiple ? 'hp-js-sortable' : '' ) . '">';

		foreach ( (array) $this->get_value() as $attachment_id ) {
			$output .= $this->render_file( $attachment_id );
		}

		$output .= '</div>';
		$output .= '<label for="' . esc_attr( $this->get_name() ) . '">';

		// Render upload button.
		$output .= '<button type="button">' . esc_html__( 'Select File', 'hivepress' ) . '</button>';

		// Render upload field.
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

	/**
	 * Renders file HTML.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string
	 */
	public function render_file( $attachment_id ) {
		$output = '<div class="hp-col-sm-2 hp-col-xs-4">';

		// Render image.
		$output .= wp_get_attachment_image( $attachment_id, 'thumbnail' );

		// Render remove button.
		$output .= '<a href="#" class="hp-js-button" data-type="remove"><i class="hp-icon fas fa-times"></i></a>';

		$output .= '</div>';

		return $output;
	}
}
