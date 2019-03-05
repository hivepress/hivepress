<?php
/**
 * Hidden field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Hidden field class.
 *
 * @class Hidden
 */
class Hidden extends Field {

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->get_attributes() ) . '>';
	}
}
