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
	 * File formats.
	 *
	 * @var array
	 */
	protected $file_formats;

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

		// todo.
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
		if ( $this->get_multiple() ) {
			$output .= '<div class="hp-row hp-js-sortable" data-nonce="' . esc_attr( wp_create_nonce( 'file_sort' ) ) . '">';
		} else {
			$output .= '<div class="hp-row">';
		}

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
				'name'         => $this->get_name(),
				'type'         => 'file',
				'multiple'     => $this->get_multiple(),
				'file_formats' => $this->get_file_formats(),
				'attributes'   => [
					'class'      => 'hp-js-file-upload',
					'data-nonce' => wp_create_nonce( 'file_upload' ),
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
		$output .= ( new \HivePress\Forms\File_Delete(
			[
				'caption'    => '<i class="hp-icon fas fa-times"></i>',
				'attributes' => [
					'data-type' => 'remove',
				],
				'values'     => [
					'attachment_id' => $attachment_id,
				],
			]
		) )->render();

		// Render ID field.
		$output .= ( new Hidden(
			[
				'name'    => 'attachment_ids[]',
				'default' => $attachment_id,
			]
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
