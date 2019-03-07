<?php
/**
 * Checkbox field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Checkbox field class.
 *
 * @class Checkbox
 */
class Checkbox extends Field {

	/**
	 * Checkbox caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'title' => esc_html__( 'Checkbox', 'hivepress' ),
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Gets checkbox caption.
	 *
	 * @return string
	 */
	protected function get_caption() {
		if ( is_null( $this->caption ) ) {
			return $this->label;
		}

		return $this->caption;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			$this->value = boolval( $this->value );
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<label for="' . esc_attr( $this->name ) . '"><input type="' . esc_attr( $this->type ) . '" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . checked( $this->value, true, false ) . ' ' . hp\html_attributes( $this->get_attributes() ) . '><span>' . hp\sanitize_html( $this->get_caption() ) . '</span></label>';
	}
}
