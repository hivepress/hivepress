<?php
/**
 * Attachment select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

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
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$attachment_id = hp_get_post_id(
				[
					'post_type' => 'attachment',
					'post__in'  => [ absint( $this->value ) ],
				]
			);

			if ( 0 !== $attachment_id ) {
				$this->value = $attachment_id;
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

		$output .= '<div>';

		if ( $this->get_value() ) {
			$output .= wp_get_attachment_image( $this->get_value() );
		}

		$output .= ( new Hidden(
			[
				'name'    => $this->get_name(),
				'default' => $this->get_value(),
			]
		) )->render();

		$output .= '<a href="#" class="hp-js-button" data-type="remove"><span class="dashicons dashicons-no-alt"></span></a>';
		$output .= '</div>';

		$output .= '<button type="button" class="button hp-js-button" data-type="file-select">' . esc_html( $this->get_caption() ) . '</button>';

		$output .= '</div>';

		return $output;
	}
}
