<?php
/**
 * Attachment upload field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

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
	 * File formats.
	 *
	 * @var array
	 */
	protected $formats = [];

	/**
	 * Multiple flag.
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
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set caption.
		if ( is_null( $this->caption ) ) {
			if ( $this->multiple ) {
				$this->caption = esc_html__( 'Select Files', 'hivepress' );
			} else {
				$this->caption = esc_html__( 'Select File', 'hivepress' );
			}
		}

		parent::boot();
	}

	/**
	 * Gets file formats.
	 *
	 * @return array
	 */
	final public function get_formats() {
		return $this->formats;
	}

	/**
	 * Checks multiple flag.
	 *
	 * @return bool
	 */
	final public function is_multiple() {
		return $this->multiple;
	}

	/**
	 * Gets maximum files.
	 *
	 * @return int
	 */
	final public function get_max_files() {
		return $this->max_files;
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( $this->multiple && ! is_null( $this->value ) ) {
			if ( [] !== $this->value ) {
				$this->value = (array) $this->value;
			} else {
				$this->value = null;
			}
		} elseif ( ! $this->multiple && is_array( $this->value ) ) {
			if ( $this->value ) {
				$this->value = reset( $this->value );
			} else {
				$this->value = null;
			}
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( $this->multiple ) {
			$this->value = array_filter( array_map( 'absint', $this->value ) );

			if ( empty( $this->value ) ) {
				$this->value = null;
			}
		} else {
			$this->value = absint( $this->value );

			if ( 0 === $this->value ) {
				$this->value = null;
			}
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			$attachment_ids = get_posts(
				[
					'post_type'      => 'attachment',
					'post__in'       => (array) $this->value,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				]
			);

			if ( count( $attachment_ids ) !== count( (array) $this->value ) ) {
				$this->add_errors( sprintf( esc_html__( '"%s" field contains an invalid value.', 'hivepress' ), $this->label ) );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Get ID.
		$id = $this->name . '_' . uniqid();

		// Render attachments.
		$output .= '<div class="hp-row" ' . ( $this->multiple ? 'data-component="sortable"' : '' ) . '>';

		if ( ! is_null( $this->value ) ) {
			foreach ( (array) $this->value as $attachment_id ) {
				$output .= $this->render_attachment( $attachment_id );
			}
		}

		$output .= '</div>';
		$output .= '<label for="' . esc_attr( $id ) . '">';

		// Render upload button.
		$output .= ( new Button(
			[
				'label'      => $this->caption,

				'attributes' => [
					'class' => [ 'button' ],
				],
			]
		) )->render();

		// Render upload field.
		$output .= ( new File(
			[
				'name'       => $this->name,
				'multiple'   => $this->multiple,
				'formats'    => $this->formats,

				'attributes' => [
					'id'             => $id,
					'data-component' => 'file-upload',
					'data-url'       => hivepress()->router->get_url( 'attachment_upload_action' ),
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
		$output = '';

		// Get attachment image.
		$image = wp_get_attachment_image( $attachment_id, 'thumbnail' );

		if ( $image ) {
			$output .= '<div class="hp-col-sm-2 hp-col-xs-4" data-url="' . esc_url( hivepress()->router->get_url( 'attachment_delete_action', [ 'attachment_id' => $attachment_id ] ) ) . '">';

			// Render attachment image.
			$output .= $image;

			// Render remove button.
			$output .= '<a href="#" data-component="file-delete"><i class="hp-icon fas fa-times"></i></a>';

			$output .= '</div>';
		}

		return $output;
	}
}
