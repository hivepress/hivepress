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
	 * Gets field attributes.
	 *
	 * @return array
	 */
	final protected function get_attributes() {

		// Set placeholder.
		if ( ! is_null( $this->placeholder ) ) {
			$this->attributes['placeholder'] = $this->placeholder;
		}

		// Set minimum length.
		if ( ! is_null( $this->min_length ) ) {
			$this->attributes['minlength'] = $this->min_length;
		}

		// Set maximum length.
		if ( ! is_null( $this->max_length ) ) {
			$this->attributes['maxlength'] = $this->max_length;
		}

		return $this->attributes;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			if ( ! is_null( $this->min_length ) && strlen( $this->value ) < $this->min_length ) {
				$this->add_errors( [ sprintf( esc_html__( '%1\$s should be at least %2\$s characters long', 'hivepress' ), $this->label, number_format_i18n( $this->min_length ) ) ] );
			}

			if ( ! is_null( $this->max_length ) && strlen( $this->value ) > $this->max_length ) {
				$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be longer than %2\$s characters", 'hivepress' ), $this->label, number_format_i18n( $this->max_length ) ) ] );
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
		return '<input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';
	}
}
