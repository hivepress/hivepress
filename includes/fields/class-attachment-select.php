<?php
/**
 * Attachment select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attachment select field class.
 *
 * @class Attachment_Select
 */
class Attachment_Select extends Field {

	/**
	 * Field meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Button caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set caption.
		if ( is_null( $this->caption ) ) {
			$this->caption = esc_html__( 'Select File', 'hivepress' );
		}

		parent::boot();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$this->value = absint( $this->value );

		if ( 0 === $this->value ) {
			$this->value = null;
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
					'post__in'       => [ $this->value ],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			);

			if ( empty( $attachment_ids ) ) {
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
		$output  = '<div ' . hp\html_attributes( $this->attributes ) . '>';
		$output .= '<div>';

		// Render attachment image.
		if ( ! is_null( $this->value ) ) {
			$output .= wp_get_attachment_image( $this->value, 'thumbnail' );
		}

		// Render remove button.
		$output .= '<a href="#" data-component="file-remove"><i class="hp-icon fas fa-times"></i></a>';

		// Render ID field.
		$output .= ( new Hidden(
			[
				'name'    => $this->name,
				'default' => $this->value,
			]
		) )->render();

		$output .= '</div>';

		// Render select button.
		$output .= ( new Button(
			[
				'label'      => $this->caption,

				'attributes' => [
					'class'          => [ 'button' ],
					'data-component' => 'file-select',
				],
			]
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
