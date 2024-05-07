<?php
/**
 * Number range field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Number range.
 */
class Number_Range extends Number {

	/**
	 * Minimum field.
	 *
	 * @var object
	 */
	protected $min_field;

	/**
	 * Maximum field.
	 *
	 * @var object
	 */
	protected $max_field;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => esc_html__( 'Number Range', 'hivepress' ),
				'editable' => false,
				'sortable' => false,

				'settings' => [
					'placeholder' => null,
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

		// Create fields.
		$this->min_field = new Number(
			array_merge(
				$this->args,
				[
					'display_type' => 'number',
					'name'         => $this->name . '[]',
					'placeholder'  => esc_html__( 'Min', 'hivepress' ),
					'required'     => false,
					'attributes'   => [],
				]
			)
		);

		$this->max_field = new Number(
			array_merge(
				$this->args,
				[
					'display_type' => 'number',
					'name'         => $this->name . '[]',
					'placeholder'  => esc_html__( 'Max', 'hivepress' ),
					'required'     => false,
					'attributes'   => [],
				]
			)
		);

		// Set component.
		if ( ! is_null( $this->min_value ) && ! is_null( $this->max_value ) ) {
			$attributes['data-component'] = 'range-slider';
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		Field::boot();
	}

	/**
	 * Gets field value for display.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) ) {
			return $this->min_field->get_display_value() . ' - ' . $this->max_field->get_display_value();
		}
	}

	/**
	 * Adds SQL filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		$this->filter['operator'] = 'BETWEEN';
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		Field::normalize();

		if ( is_array( $this->value ) && count( $this->value ) === 2 ) {
			sort( $this->value );
		} else {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {

		// Set field values.
		$this->min_field->set_value( hp\get_first_array_value( $this->value ) );
		$this->max_field->set_value( hp\get_last_array_value( $this->value ) );

		// Set range value.
		if ( ! is_null( $this->min_field->get_value() ) && ! is_null( $this->max_field->get_value() ) ) {
			$this->value = [ $this->min_field->get_value(), $this->max_field->get_value() ];
		} else {
			$this->value = null;
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( Field::validate() && ! is_null( $this->value ) ) {

			// Validate fields.
			$this->min_field->validate();
			$this->max_field->validate();

			// Add errors.
			$this->add_errors( array_unique( array_merge( $this->min_field->get_errors(), $this->max_field->get_errors() ) ) );
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Render fields.
		$output .= $this->min_field->render();
		$output .= $this->max_field->render();

		$output .= '</div>';

		return $output;
	}
}
