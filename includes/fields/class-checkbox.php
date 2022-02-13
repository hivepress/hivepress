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
 * Checkbox.
 */
class Checkbox extends Field {

	/**
	 * Checkbox caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Checked value.
	 *
	 * @var mixed
	 */
	protected $check_value = true;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'Checkbox', 'hivepress' ),
				'filterable' => true,

				'settings'   => [
					'caption' => [
						'label'      => esc_html__( 'Caption', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 100,
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'statuses' => [
					'optional' => null,
				],
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set caption.
		if ( is_null( $this->caption ) ) {
			$this->caption = $this->label;
		}

		parent::boot();
	}

	/**
	 * Gets field value for display.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		return $this->value ? esc_html__( 'Yes', 'hivepress' ) : esc_html__( 'No', 'hivepress' );
	}

	/**
	 * Adds SQL filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		unset( $this->filter['value'] );

		$this->filter['operator'] = 'EXISTS';
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( ! is_null( $this->value ) ) {
			$this->value = trim( wp_unslash( $this->value ) );
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( is_bool( $this->check_value ) ) {
			$this->value = boolval( $this->value );
		} else {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Get ID.
		$id = sanitize_key( $this->name ) . '_' . uniqid();

		// Get attributes.
		$attributes = [];

		if ( $this->disabled ) {
			$attributes['disabled'] = true;
		}

		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Render field.
		$output = '<label for="' . esc_attr( $id ) . '" ' . hp\html_attributes( $this->attributes ) . '>';

		$output .= '<input type="checkbox" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $this->check_value ) . '" ' . checked( $this->value, $this->check_value, false ) . ' ' . hp\html_attributes( $attributes ) . '>';
		$output .= '<span>' . hp\sanitize_html( $this->caption ) . '</span>';

		$output .= '</label>';

		return $output;
	}
}
