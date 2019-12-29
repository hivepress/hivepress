<?php
/**
 * Number field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Number field class.
 *
 * @class Number
 */
class Number extends Field {

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Decimals number.
	 *
	 * @var int
	 */
	protected $decimals = 0;

	/**
	 * Minimum value.
	 *
	 * @var int
	 */
	protected $min_value;

	/**
	 * Maximum value.
	 *
	 * @var int
	 */
	protected $max_value;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'Number', 'hivepress' ),
				'type'       => 'DECIMAL',
				'filterable' => true,

				'settings'   => [
					'placeholder' => [
						'label'      => esc_html__( 'Placeholder', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 2048,
						'_order'     => 10,
					],

					'decimals'    => [
						'label'     => esc_html__( 'Decimals', 'hivepress' ),
						'type'      => 'number',
						'default'   => 0,
						'min_value' => 0,
						'max_value' => 6,
						'_order'    => 20,
					],

					'min_value'   => [
						'label'  => esc_html__( 'Minimum Value', 'hivepress' ),
						'type'   => 'number',
						'_order' => 30,
					],

					'max_value'   => [
						'label'  => esc_html__( 'Maximum Value', 'hivepress' ),
						'type'   => 'number',
						'_order' => 40,
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set placeholder.
		if ( ! is_null( $this->placeholder ) ) {
			$attributes['placeholder'] = $this->placeholder;
		}

		// Set step.
		$attributes['step'] = (float) sprintf( '%f', 1 / pow( 10, $this->decimals ) );

		// Set minimum value.
		if ( ! is_null( $this->min_value ) ) {
			$attributes['min'] = $this->min_value;
		}

		// Set maximum value.
		if ( ! is_null( $this->max_value ) ) {
			$attributes['max'] = $this->max_value;
		}

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
		if ( ! is_null( $this->value ) ) {
			return number_format_i18n( $this->value, strlen( substr( strrchr( (string) $this->value, '.' ), 1 ) ) );
		}
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( ! is_numeric( $this->value ) ) {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( $this->decimals > 0 ) {
			$this->value = round( floatval( $this->value ), $this->decimals );
		} else {
			$this->value = intval( $this->value );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			if ( ! is_null( $this->min_value ) && $this->value < $this->min_value ) {
				$this->add_errors( sprintf( esc_html__( '"%1$s" can\'t be lower than %2$s.', 'hivepress' ), $this->label, number_format_i18n( $this->min_value ) ) );
			}

			if ( ! is_null( $this->max_value ) && $this->value > $this->max_value ) {
				$this->add_errors( sprintf( esc_html__( '"%1$s" can\'t be greater than %2$s.', 'hivepress' ), $this->label, number_format_i18n( $this->max_value ) ) );
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
		return '<input type="' . esc_attr( $this->display_type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
