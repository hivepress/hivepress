<?php
/**
 * File field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * File selection.
 */
class File extends Field {

	/**
	 * Allowed file formats.
	 *
	 * @var array
	 */
	protected $formats = [];

	/**
	 * Allow multiple files?
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set file formats.
		if ( $this->formats ) {
			$attributes['accept'] = '.' . implode( ',.', $this->formats );
		}

		// Set multiple flag.
		if ( $this->multiple ) {
			$attributes['multiple'] = true;
		}

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_array( $this->value ) || ! isset( $this->value['tmp_name'] ) || ! isset( $this->value['name'] ) ) {
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
			if ( $this->required && ( ! $this->value['tmp_name'] || ! $this->value['name'] ) ) {

				/* translators: %s: field label. */
				$this->add_errors( sprintf( esc_html__( '"%s" field is required.', 'hivepress' ), $this->get_label( true ) ) );
			} elseif ( $this->formats && ! hivepress()->attachment->is_valid_file( $this->value['tmp_name'], $this->value['name'], $this->formats ) ) {

				/* translators: %s: file extensions. */
				$this->add_errors( sprintf( esc_html__( 'Only %s files are allowed.', 'hivepress' ), strtoupper( implode( ', ', $this->formats ) ) ) );
			}
		}

		return ! $this->errors;
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<input type="' . esc_attr( $this->display_type ) . '" name="' . esc_attr( $this->name ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
