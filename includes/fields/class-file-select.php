<?php
/**
 * File select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File select field class.
 *
 * @class File_Select
 */
class File_Select extends Field {

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$attachment_id = hp_get_post_id(
				[
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'post__in'    => [ absint( $this->value ) ],
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
		$output = '<div ' . hp_html_attributes( $this->attributes ) . '>';

		// todo.
		$output .= '</div>';

		return $output;
	}
}
