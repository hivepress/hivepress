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
 * File field class.
 *
 * @class File
 */
class File extends Field {

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
		if ( ! is_array( $this->value ) || ! isset( $this->value['tmp_name'], $this->value['name'] ) ) {
			$this->value = null;
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && $this->formats ) {

			// Check file format.
			$file_type    = wp_check_filetype_and_ext( $this->value['tmp_name'], $this->value['name'] );
			$file_formats = array_map( 'strtoupper', $this->formats );

			if ( ! $file_type['ext'] || ! in_array( strtoupper( $file_type['ext'] ), $file_formats, true ) ) {

				/* translators: %s: file extensions. */
				$this->add_errors( sprintf( esc_html__( 'Only %s files are allowed.', 'hivepress' ), implode( ', ', $file_formats ) ) );
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

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {

		if ( parent::validate() && is_null( $this->value ) ) {
			// Check file format.
			if ( $this->formats ) {
				$file_type    = wp_check_filetype_and_ext( $this->value['tmp_name'], $this->value['name'] );
				$file_formats = array_map( 'strtoupper', $this->formats );

				if ( ! $file_type['ext'] || ! in_array( strtoupper( $file_type['ext'] ), $file_formats, true ) ) {
					/* translators: %s: field label. */
					$this->add_errors( sprintf( esc_html__( 'Only files %1$s are allowed in %2$s.', 'hivepress' ), implode( ', ', $file_formats ), $this->label ) );
				}
			}
		}

		return empty( $this->errors );
	}
}
