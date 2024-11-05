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
 * Number.
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
	 * @var float
	 */
	protected $min_value;

	/**
	 * Maximum value.
	 *
	 * @var float
	 */
	protected $max_value;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'Number', 'hivepress' ),
				'type'       => 'DECIMAL',
				'filterable' => true,
				'sortable'   => true,

				'settings'   => [
					'placeholder' => [
						'label'      => esc_html__( 'Placeholder', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 100,
					],

					'decimals'    => [
						'label'     => esc_html_x( 'Decimals', 'quantity', 'hivepress' ),
						'type'      => 'number',
						'default'   => 0,
						'min_value' => 0,
						'max_value' => 6,
						'required'  => true,
						'_order'    => 110,
					],

					'min_value'   => [
						'label'    => esc_html__( 'Minimum Value', 'hivepress' ),
						'type'     => 'number',
						'decimals' => 6,
						'_order'   => 120,
					],

					'max_value'   => [
						'label'    => esc_html__( 'Maximum Value', 'hivepress' ),
						'type'     => 'number',
						'decimals' => 6,
						'_order'   => 130,
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

		// Set disabled flag.
		if ( $this->disabled ) {
			$attributes['disabled'] = true;
		}

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Set component.
		$attributes['data-component'] = 'number';

		// Set attributes.
		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Gets field value for display.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) ) {
			return hp\format_number( $this->value, $this->decimals );
		}
	}

	/**
	 * Adds SQL filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		if ( $this->decimals ) {
			$this->filter['type'] .= '(10,' . $this->decimals . ')';
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
		if ( $this->decimals ) {
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
				/* translators: 1: field label, 2: number. */
				$this->add_errors( sprintf( esc_html__( '"%1$s" can\'t be lower than %2$s.', 'hivepress' ), $this->get_label( true ), hp\format_number( $this->min_value ) ) );
			}

			if ( ! is_null( $this->max_value ) && $this->value > $this->max_value ) {
				/* translators: 1: field label, 2: number. */
				$this->add_errors( sprintf( esc_html__( '"%1$s" can\'t be greater than %2$s.', 'hivepress' ), $this->get_label( true ), hp\format_number( $this->max_value ) ) );
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
