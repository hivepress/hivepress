<?php
/**
 * Attachment upload field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attachment upload field class.
 *
 * @class Attachment_Upload
 */
class Attachment_Upload extends Field {

	/**
	 * Button caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Multiple property.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Maximum files.
	 *
	 * @var int
	 */
	protected $max_files;

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
		if ( ! is_null( $this->value ) && [] !== $this->value ) {
			$attachment_ids = get_posts(
				[
					'post_type'      => 'attachment',
					'post__in'       => array_map( 'absint', (array) $this->value ),
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				]
			);

			if ( ! empty( $attachment_ids ) ) {
				$this->value = $attachment_ids;
			} else {
				$this->value = null;
			}
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp_html_attributes( $this->get_attributes() ) . '>';

		// Render files.
		if ( $this->multiple ) {
			$output .= '<div class="hp-row hp-js-sortable">';
		} else {
			$output .= '<div class="hp-row">';
		}

		foreach ( (array) $this->value as $attachment_id ) {
			$output .= $this->render_attachment( $attachment_id );
		}

		$output .= '</div>';
		$output .= '<label for="' . esc_attr( $this->name ) . '">';

		// Render upload button.
		$output .= '<button type="button">' . esc_html( $this->caption ) . '</button>';

		// Render upload field.
		$output .= ( new File(
			[
				'name'         => $this->name,
				'type'         => 'file',
				'multiple'     => $this->multiple,
				'file_formats' => $this->file_formats,
				'attributes'   => [
					'class'    => 'hp-js-file-upload',
					'data-url' => hp_get_rest_url( '/attachments' ),
				],
			]
		) )->render();

		$output .= '</label>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Renders attachment HTML.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string
	 */
	public function render_attachment( $attachment_id ) {
		$output = '<div class="hp-col-sm-2 hp-col-xs-4" data-url="' . esc_url( hp_get_rest_url( '/attachments/' . $attachment_id ) ) . '">';

		// Render image.
		$output .= wp_get_attachment_image( $attachment_id, 'thumbnail' );

		// Render remove button.
		$output .= '<a href="#" class="hp-js-button" data-type="remove request" data-url="' . esc_url( hp_get_rest_url( '/attachments/' . $attachment_id ) ) . '" data-method="DELETE" data-params="' . esc_attr(
			wp_json_encode(
				[
					'id' => $attachment_id,
				]
			)
		) . '"><i class="hp-icon fas fa-times"></i></a>';

		$output .= '</div>';

		return $output;
	}
}
