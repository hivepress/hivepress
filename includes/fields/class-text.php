<?php
/**
 * Text field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Text field class.
 *
 * @class Text
 */
class Text extends Field {

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Minimum length.
	 *
	 * @var int
	 */
	protected $min_length;

	/**
	 * Maximum length.
	 *
	 * @var int
	 */
	protected $max_length;

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Validate field value.
	 */
	public function validate() {
		parent::validate();

		if ( ! is_null( $this->value ) ) {
			if ( ! is_null( $this->min_length ) && strlen( $this->value ) < $this->min_length ) {
				$this->errors[] = hp_sanitize_html( __( '%1\$s should be at least %2\$s characters long.', 'hivepress' ), '<strong>' . $this->get_label() . '</strong>', number_format_i18n( $this->min_length ) );
			}

			if ( ! is_null( $this->max_length ) && strlen( $this->value ) > $this->max_length ) {
				$this->errors[] = hp_sanitize_html( __( "%1\$s can't be longer than %2\$s characters.", 'hivepress' ), '<strong>' . $this->get_label() . '</strong>', number_format_i18n( $this->max_length ) );
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
		return '<input type="' . esc_attr( $this->get_type() ) . '" name="' . esc_attr( $this->get_name() ) . '" value="' . esc_attr( $this->get_value() ) . '" minlength="' . esc_attr( $this->get_min_length() ) . '" maxlength="' . esc_attr( $this->get_max_length() ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';
	}
}
