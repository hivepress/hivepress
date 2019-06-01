<?php
/**
 * Button field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Button field class.
 *
 * @class Button
 */
class Button extends Field {

	/**
	 * Field type.
	 *
	 * @var string
	 */
	protected static $type;

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
		return '<button type="submit" ' . hp\html_attributes( $this->attributes ) . '>' . esc_html( $this->label ) . '</button>';
	}
}
