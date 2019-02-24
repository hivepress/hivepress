<?php
/**
 * Form button.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form button class.
 *
 * @class Button
 */
class Button extends Form {

	/**
	 * Button caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Gets form attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {

		// Set class.
		$this->attributes['class'] = 'hp-js-button ' . hp_get_array_value( $this->attributes, 'class' );

		// Set type.
		$this->attributes['data-type'] = 'submit ' . hp_get_array_value( $this->attributes, 'data-type' );

		// Set name.
		$this->attributes['data-name'] = $this->get_name();

		// Set nonce.
		$this->attributes['data-nonce'] = $this->get_value( 'nonce' );

		// Set values.
		$this->attributes['data-values'] = wp_json_encode( $this->get_values() );

		return $this->attributes;
	}

	/**
	 * Renders form HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$output = '<a href="#" ' . hp_html_attributes( $this->get_attributes() ) . '>' . hp_sanitize_html( $this->get_caption() ) . '</a>';

		return $output;
	}
}
