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
	 * Sample value.
	 *
	 * @var mixed
	 */
	protected $sample = true;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
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
						'max_length' => 2048,
						'_order'     => 10,
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
			[
				'statuses' => [
					'optional' => null,
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set caption.
		if ( is_null( $this->caption ) ) {
			$this->caption = $this->label;
		}

		// Set ID.
		$attributes['id'] = explode( '[', $this->name )[0] . '_' . uniqid();

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Gets field display value.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		return $this->value ? esc_html__( 'Yes', 'hivepress' ) : esc_html__( 'No', 'hivepress' );
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( ! is_null( $this->value ) ) {
			$this->value = wp_unslash( $this->value );
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( is_bool( $this->sample ) ) {
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
		$output = '<label for="' . esc_attr( hp\get_array_value( $this->attributes, 'id' ) ) . '" class="' . esc_attr( implode( ' ', (array) hp\get_array_value( $this->attributes, 'class' ) ) ) . '">';

		unset( $this->attributes['class'] );

		$output .= '<input type="' . esc_attr( $this->display_type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->sample ) . '" ' . checked( $this->value, $this->sample, false ) . ' ' . hp\html_attributes( $this->attributes ) . '>';
		$output .= '<span>' . hp\sanitize_html( $this->caption ) . '</span>';

		$output .= '</label>';

		return $output;
	}
}
