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
	 * Button caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {

		// Set caption.
		if ( is_null( $this->caption ) ) {
			$this->caption = esc_html__( 'Select File', 'hivepress' );
		}

		parent::bootstrap();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$attachment_ids = get_posts(
			[
				'post_type'      => 'attachment',
				'post__in'       => [ absint( $this->value ) ],
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		if ( ! empty( $attachment_ids ) ) {
			$this->value = reset( $attachment_ids );
		} else {
			$this->value = null;
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output  = '<div ' . hp\html_attributes( $this->attributes ) . '>';
		$output .= '<div>';

		if ( ! is_null( $this->value ) ) {
			$output .= wp_get_attachment_image( $this->value, 'thumbnail' );
		}

		$output .= ( new Hidden(
			[
				'name'    => $this->name,
				'default' => $this->value,
			]
		) )->render();

		$output .= '<a href="#" data-component="file-remove"><span class="dashicons dashicons-no-alt"></span></a>';
		$output .= '</div>';

		$output .= '<button type="button" class="button" data-component="file-select">' . esc_html( $this->caption ) . '</button>';
		$output .= '</div>';

		return $output;
	}
}
