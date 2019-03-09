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
	 * Field type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Field title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * File formats.
	 *
	 * @var array
	 */
	protected $file_formats = [];

	/**
	 * Multiple property.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Gets field attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		$attributes = [];

		// Set multiple property.
		if ( $this->multiple ) {
			$attributes['multiple'] = true;
		}

		// Set file formats.
		if ( ! empty( $this->file_formats ) ) {
			$attributes['accept'] = '.' . implode( ',.', $this->file_formats );
		}

		return hp\merge_arrays( parent::get_attributes(), $attributes );
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
		return '<input type="' . esc_attr( static::$type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->get_attributes() ) . '>';
	}
}
