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
	 * Field meta.
	 *
	 * @var array
	 */
	protected static $meta;

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
	protected function sanitize() {}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<input type="' . esc_attr( $this->display_type ) . '" name="' . esc_attr( $this->name ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
