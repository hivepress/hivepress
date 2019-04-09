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
	 * Field type.
	 *
	 * @var string
	 */
	protected static $type;

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
	protected $file_formats = [];

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
	 * Gets file formats.
	 *
	 * @return array
	 */
	final public function get_file_formats() {
		return $this->file_formats;
	}

	/**
	 * Checks multiple property.
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
		return absint( $this->max_files );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set caption.
		if ( is_null( $this->caption ) ) {
			if ( $this->multiple ) {
				$this->caption = esc_html__( 'Select Files', 'hivepress' );
			} else {
				$this->caption = esc_html__( 'Select File', 'hivepress' );
			}
		}

		// Set component.
		$attributes['data-component'] = 'file-manager';

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$attachment_ids = get_posts(
				[
					'post_type'      => 'attachment',
					'post__in'       => array_merge( [ 0 ], array_map( 'absint', (array) $this->value ) ),
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				]
			);

			if ( ! empty( $attachment_ids ) ) {
				if ( $this->multiple ) {
					$this->value = $attachment_ids;
				} else {
					$this->value = reset( $attachment_ids );
				}
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
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Render attachments.
		if ( $this->multiple ) {
			$output .= '<div class="hp-row" data-component="sortable">';
		} else {
			$output .= '<div class="hp-row">';
		}

		if ( ! is_null( $this->value ) ) {
			foreach ( (array) $this->value as $attachment_id ) {
				$output .= $this->render_attachment( $attachment_id );
			}
		}

		$output .= '</div>';
		$output .= '<label for="' . esc_attr( $this->name ) . '">';

		// Render upload button.
		$output .= '<button type="button">' . esc_html( $this->caption ) . '</button>';

		// Render upload field.
		$output .= ( new File(
			[
				'name'         => $this->name,
				'multiple'     => $this->multiple,
				'file_formats' => $this->file_formats,
				'attributes'   => [
					'data-component' => 'file-upload',
					'data-url'       => hp\get_rest_url( '/attachments' ),
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
		$output = '<div class="hp-col-sm-2 hp-col-xs-4" data-url="' . esc_url( hp\get_rest_url( '/attachments/' . $attachment_id ) ) . '">';

		// Render attachment image.
		$output .= wp_get_attachment_image( $attachment_id, 'thumbnail' );

		// Render remove button.
		$output .= '<a href="#" data-component="file-delete"><i class="hp-icon fas fa-times"></i></a>';
		$output .= '</div>';

		return $output;
	}
}
